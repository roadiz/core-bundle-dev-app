<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryNodesSourcesEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\CoreBundle\Logger\Entity\Log;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\NodeSourceSearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchResultsInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SolrSearchResultItem;
use RZ\Roadiz\CoreBundle\SearchEngine\SolrSearchResults;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * EntityRepository that implements search engine query with Solr.
 *
 * @template T of NodesSources
 *
 * @extends StatusAwareRepository<T|NodesSources>
 *
 * @template-extends StatusAwareRepository<T|NodesSources>
 */
class NodesSourcesRepository extends StatusAwareRepository
{
    private ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler;

    /**
     * @param class-string<NodesSources> $entityClass
     */
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        ?NodeSourceSearchHandlerInterface $nodeSourceSearchHandler,
        string $entityClass = NodesSources::class,
    ) {
        parent::__construct($registry, $entityClass, $previewResolver, $dispatcher, $security);
        $this->nodeSourceSearchHandler = $nodeSourceSearchHandler;
    }

    /**
     * @return Event
     */
    protected function dispatchQueryBuilderBuildEvent(QueryBuilder $qb, string $property, mixed $value): object
    {
        // @phpstan-ignore-next-line
        return $this->dispatcher->dispatch(
            new QueryBuilderNodesSourcesBuildEvent($qb, $property, $value, $this->getEntityName())
        );
    }

    /**
     * @return Event
     */
    protected function dispatchQueryBuilderApplyEvent(QueryBuilder $qb, string $property, mixed $value): object
    {
        // @phpstan-ignore-next-line
        return $this->dispatcher->dispatch(
            new QueryBuilderNodesSourcesApplyEvent($qb, $property, $value, $this->getEntityName())
        );
    }

    /**
     * @return Event
     */
    protected function dispatchQueryEvent(Query $query): object
    {
        // @phpstan-ignore-next-line
        return $this->dispatcher->dispatch(
            new QueryNodesSourcesEvent($query, $this->getEntityName())
        );
    }

    /**
     * Add a tag filtering to queryBuilder.
     */
    protected function filterByTag(array &$criteria, QueryBuilder $qb): void
    {
        if (key_exists('tags', $criteria)) {
            if (!$this->hasJoinedNode($qb, static::NODESSOURCES_ALIAS)) {
                $qb->innerJoin(
                    static::NODESSOURCES_ALIAS.'.node',
                    static::NODE_ALIAS
                );
            }

            $this->buildTagFiltering($criteria, $qb);
        }
    }

    /**
     * Reimplementing findBy features… with extra things.
     *
     * * key => array('<=', $value)
     * * key => array('<', $value)
     * * key => array('>=', $value)
     * * key => array('>', $value)
     * * key => array('BETWEEN', $value, $value)
     * * key => array('LIKE', $value)
     * * key => array('NOT IN', $array)
     * * key => 'NOT NULL'
     *
     * You even can filter with node fields, examples:
     *
     * * `node.published => true`
     * * `node.nodeName => 'page1'`
     */
    protected function filterByCriteria(
        array &$criteria,
        QueryBuilder $qb,
    ): void {
        $simpleQB = new SimpleQueryBuilder($qb);
        /*
         * Reimplementing findBy features…
         */
        foreach ($criteria as $key => $value) {
            if ('tags' == $key || 'tagExclusive' == $key) {
                continue;
            }
            /**
             * Main QueryBuilder dispatch loop for
             * custom properties criteria.
             *
             * @var QueryBuilderNodesSourcesBuildEvent $event
             */
            $event = $this->dispatchQueryBuilderBuildEvent($qb, $key, $value);

            if (!$event->isPropagationStopped()) {
                /*
                 * compute prefix for
                 * filtering node relation fields
                 */
                $prefix = static::NODESSOURCES_ALIAS.'.';
                // Dots are forbidden in field definitions
                $baseKey = $simpleQB->getParameterKey($key);
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $prefix, $key, $baseKey));
            }
        }
    }

    /**
     * Bind parameters to generated query.
     */
    protected function applyFilterByCriteria(array &$criteria, QueryBuilder $qb): void
    {
        /*
         * Reimplementing findBy features…
         */
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            if ('tags' == $key || 'tagExclusive' == $key) {
                continue;
            }

            /** @var QueryBuilderNodesSourcesApplyEvent $event */
            $event = $this->dispatchQueryBuilderApplyEvent($qb, $key, $value);
            if (!$event->isPropagationStopped()) {
                $simpleQB->bindValue($key, $value);
            }
        }
    }

    public function alterQueryBuilderWithAuthorizationChecker(
        QueryBuilder $qb,
        string $prefix = EntityRepository::NODESSOURCES_ALIAS,
    ): QueryBuilder {
        if (true === $this->isDisplayingAllNodesStatuses()) {
            if (!$this->hasJoinedNode($qb, $prefix)) {
                $qb->innerJoin($prefix.'.node', static::NODE_ALIAS);
            }

            return $qb;
        }

        if (true === $this->isDisplayingNotPublishedNodes() || $this->previewResolver->isPreview()) {
            /*
             * Forbid deleted node for backend user when authorizationChecker not null.
             */
            if (!$this->hasJoinedNode($qb, $prefix)) {
                $qb->innerJoin($prefix.'.node', static::NODE_ALIAS);
            }
            $qb->andWhere($qb->expr()->lte(static::NODE_ALIAS.'.status', ':node_status'));
        } else {
            /*
             * Forbid unpublished node for anonymous and not backend users.
             */
            if (!$this->hasJoinedNode($qb, $prefix)) {
                $qb->innerJoin($prefix.'.node', static::NODE_ALIAS);
            }
            $qb->andWhere($qb->expr()->eq(static::NODE_ALIAS.'.status', ':node_status'));
        }
        $qb->setParameter('node_status', NodeStatus::PUBLISHED);

        return $qb;
    }

    /**
     * Create a secure query with node.published = true if user is
     * not a Backend user.
     */
    protected function getContextualQuery(
        array &$criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $this->alterQueryBuilderWithAuthorizationChecker($qb, static::NODESSOURCES_ALIAS);
        $qb->addSelect(static::NODE_ALIAS);
        /*
         * Filtering by tag
         */
        $this->filterByTag($criteria, $qb);
        $this->filterByCriteria($criteria, $qb);

        // Add ordering
        if (null !== $orderBy) {
            foreach ($orderBy as $key => $value) {
                if (\str_contains($key, 'node.')) {
                    $simpleKey = str_replace('node.', '', $key);
                    $qb->addOrderBy(static::NODE_ALIAS.'.'.$simpleKey, $value);
                } else {
                    $qb->addOrderBy(static::NODESSOURCES_ALIAS.'.'.$key, $value);
                }
            }
        }

        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        return $qb;
    }

    /**
     * Create a secured count query with node.published = true if user is
     * not a Backend user and if authorizationChecker is defined.
     *
     * This method allows to pre-filter Nodes with a given translation.
     *
     * @return QueryBuilder
     */
    protected function getCountContextualQuery(array &$criteria)
    {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $this->alterQueryBuilderWithAuthorizationChecker($qb, static::NODESSOURCES_ALIAS);
        /*
         * Filtering by tag
         */
        $this->filterByTag($criteria, $qb);
        $this->filterByCriteria($criteria, $qb);

        return $qb->select($qb->expr()->countDistinct(static::NODESSOURCES_ALIAS.'.id'));
    }

    /**
     * Just like the countBy method but with relational criteria.
     *
     * @param array $criteria
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws NonUniqueResultException
     */
    public function countBy(mixed $criteria): int
    {
        $query = $this->getCountContextualQuery($criteria);
        $this->dispatchQueryBuilderEvent($query, $this->getEntityName());
        $this->applyFilterByTag($criteria, $query);
        $this->applyFilterByCriteria($criteria, $query);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * A secure findBy with which user must be a backend user
     * to see unpublished nodes.
     *
     * Reimplementing findBy features with extra things.
     *
     * * key => array('<=', $value)
     * * key => array('<', $value)
     * * key => array('>=', $value)
     * * key => array('>', $value)
     * * key => array('BETWEEN', $value, $value)
     * * key => array('LIKE', $value)
     * * key => array('NOT IN', $array)
     * * key => 'NOT NULL'
     *
     * You even can filter with node fields, examples:
     *
     * * `node.published => true`
     * * `node.nodeName => 'page1'`
     *
     * Or filter by tags:
     *
     * * `tags => $tag1`
     * * `tags => [$tag1, $tag2]`
     * * `tags => [$tag1, $tag2], tagExclusive => true`
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array<NodesSources>
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
    ): array {
        $qb = $this->getContextualQuery(
            $criteria,
            $orderBy,
            $limit,
            $offset
        );
        /*
         * Eagerly fetch UrlAliases
         * to limit SQL query count
         */
        $qb->leftJoin(static::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->addSelect('ua')
        ;
        $qb->setCacheable(true);
        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByTag($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        if (
            null !== $limit
            && null !== $offset
        ) {
            /*
             * We need to use Doctrine paginator
             * if a limit is set because of the default inner join
             */
            return (new Paginator($query))->getIterator()->getArrayCopy();
        } else {
            return $query->getResult();
        }
    }

    /**
     * A secure findOneBy with which user must be a backend user
     * to see unpublished nodes.
     *
     * @throws NonUniqueResultException
     */
    public function findOneBy(
        array $criteria,
        ?array $orderBy = null,
    ): ?NodesSources {
        $qb = $this->getContextualQuery(
            $criteria,
            $orderBy,
            1,
            null
        );
        /*
         * Eagerly fetch UrlAliases
         * to limit SQL query count
         */
        $qb->leftJoin(static::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->addSelect('ua')
        ;
        $qb->setCacheable(true);
        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByTag($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getOneOrNullResult();
    }

    /**
     * Search nodes sources by using Solr search engine.
     *
     * @param string $query Solr query string (for example: `text:Lorem Ipsum`)
     * @param int    $limit Result number to fetch (default: all)
     *
     * @return array<SolrSearchResultItem<NodesSources>>
     */
    public function findBySearchQuery(string $query, int $limit = 25): array
    {
        if (null !== $this->nodeSourceSearchHandler) {
            try {
                $this->nodeSourceSearchHandler->boostByUpdateDate();
                $arguments = [];
                if ($this->isDisplayingNotPublishedNodes()) {
                    $arguments['status'] = ['<=', NodeStatus::PUBLISHED];
                }
                if ($this->isDisplayingAllNodesStatuses()) {
                    $arguments['status'] = ['<=', NodeStatus::DELETED];
                }

                if ($limit > 0) {
                    return $this->nodeSourceSearchHandler->search($query, $arguments, $limit)->getResultItems();
                }

                return $this->nodeSourceSearchHandler->search($query, $arguments, 999999)->getResultItems();
            } catch (SolrServerNotAvailableException $exception) {
                return [];
            }
        }

        return [];
    }

    /**
     * Search nodes sources by using Solr search engine
     * and a specific translation.
     *
     * @param string               $query       Solr query string (for example: `text:Lorem Ipsum`)
     * @param TranslationInterface $translation Current translation
     * @param int                  $limit
     *
     * @return SearchResultsInterface
     */
    public function findBySearchQueryAndTranslation($query, TranslationInterface $translation, $limit = 25)
    {
        if (null !== $this->nodeSourceSearchHandler) {
            try {
                $params = [
                    'translation' => $translation,
                ];

                if ($limit > 0) {
                    return $this->nodeSourceSearchHandler->search($query, $params, $limit);
                } else {
                    return $this->nodeSourceSearchHandler->search($query, $params, 999999);
                }
            } catch (SolrServerNotAvailableException $exception) {
                return new SolrSearchResults([], $this->_em);
            }
        }

        return new SolrSearchResults([], $this->_em);
    }

    /**
     * Search Nodes-Sources using LIKE condition on title
     * meta-title, meta-keywords, meta-description.
     *
     * @return array
     */
    public function findByTextQuery(
        string $textQuery,
        int $limit = 0,
        array $nodeTypes = [],
        bool $onlyVisible = false,
        array $additionalCriteria = [],
    ) {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $qb->addSelect(static::NODE_ALIAS)
            ->addSelect('ua')
            ->leftJoin(static::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->like(static::NODESSOURCES_ALIAS.'.title', ':query'),
                $qb->expr()->like(static::NODESSOURCES_ALIAS.'.metaTitle', ':query'),
                $qb->expr()->like(static::NODESSOURCES_ALIAS.'.metaDescription', ':query')
            ))
            ->orderBy(static::NODESSOURCES_ALIAS.'.title', 'ASC')
            ->setParameter(':query', '%'.$textQuery.'%');

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        /*
         * Alteration always join node table.
         */
        $this->alterQueryBuilderWithAuthorizationChecker($qb, static::NODESSOURCES_ALIAS);

        if (count($nodeTypes) > 0) {
            $additionalCriteria['node.nodeType'] = $nodeTypes;
        }

        if (true === $onlyVisible) {
            $additionalCriteria['node.visible'] = true;
        }

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());

        if (count($additionalCriteria) > 0) {
            $this->prepareComparisons($additionalCriteria, $qb, static::NODESSOURCES_ALIAS);
            $this->applyFilterByCriteria($additionalCriteria, $qb);
        }

        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getResult();
    }

    /**
     * Find latest updated NodesSources using Log table.
     */
    public function findByLatestUpdated(int $maxResult = 5): Paginator
    {
        $subQuery = $this->_em->createQueryBuilder();
        $subQuery->select('slog.entityId')
            ->from(Log::class, 'slog')
            ->andWhere($subQuery->expr()->eq('slog.entityClass', ':entityClass'))
            ->orderBy('slog.datetime', 'DESC');

        $query = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $query
            ->andWhere($query->expr()->in(static::NODESSOURCES_ALIAS.'.id', $subQuery->getQuery()->getDQL()))
            ->setParameter(':entityClass', NodesSources::class)
            ->setMaxResults($maxResult)
        ;

        return new Paginator($query->getQuery());
    }

    /**
     * Get node-source parent according to its translation.
     *
     * @return NodesSources|null
     *
     * @throws NonUniqueResultException
     */
    public function findParent(NodesSources $nodeSource)
    {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $qb->select(static::NODESSOURCES_ALIAS.', n, ua')
            ->innerJoin(static::NODESSOURCES_ALIAS.'.node', static::NODE_ALIAS)
            ->innerJoin('n.children', 'cn')
            ->leftJoin(static::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->andWhere($qb->expr()->eq('cn.id', ':childNodeId'))
            ->andWhere($qb->expr()->eq(static::NODESSOURCES_ALIAS.'.translation', ':translation'))
            ->setParameter('childNodeId', $nodeSource->getNode()->getId())
            ->setParameter('translation', $nodeSource->getTranslation())
            ->setMaxResults(1)
            ->setCacheable(true);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return mixed|null
     */
    public function findOneByNodeAndTranslation(Node $node, ?TranslationInterface $translation)
    {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);

        $qb->select(static::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq(static::NODESSOURCES_ALIAS.'.node', ':node'))
            ->setMaxResults(1)
            ->setParameter('node', $node)
            ->setCacheable(true);

        if (null !== $translation) {
            $qb->andWhere($qb->expr()->eq(static::NODESSOURCES_ALIAS.'.translation', ':translation'))
                ->setParameter('translation', $translation);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     *
     * Extends EntityRepository to make join possible with «node.» prefix.
     * Required if making search with EntityListManager and filtering by node criteria.
     */
    protected function prepareComparisons(array &$criteria, QueryBuilder $qb, string $alias): QueryBuilder
    {
        $simpleQB = new SimpleQueryBuilder($qb);

        foreach ($criteria as $key => $value) {
            /*
             * Main QueryBuilder dispatch loop for
             * custom properties criteria.
             */
            $event = $this->dispatchQueryBuilderBuildEvent($qb, $key, $value);

            if (!$event->isPropagationStopped()) {
                $baseKey = $simpleQB->getParameterKey($key);
                if (\str_contains($key, 'node.nodeType.')) {
                    if (!$this->hasJoinedNode($qb, $alias)) {
                        $qb->innerJoin($alias.'.node', static::NODE_ALIAS);
                    }
                    if (!$this->hasJoinedNodeType($qb, $alias)) {
                        $qb->innerJoin(static::NODE_ALIAS.'.nodeType', static::NODETYPE_ALIAS);
                    }
                    $prefix = static::NODETYPE_ALIAS.'.';
                    $simpleKey = str_replace('node.nodeType.', '', $key);
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $prefix, $simpleKey, $baseKey));
                } elseif (\str_contains($key, 'node.')) {
                    if (!$this->hasJoinedNode($qb, $alias)) {
                        $qb->innerJoin($alias.'.node', static::NODE_ALIAS);
                    }
                    $prefix = static::NODE_ALIAS.'.';
                    $simpleKey = str_replace('node.', '', $key);
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $prefix, $simpleKey, $baseKey));
                } else {
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $alias.'.', $key, $baseKey));
                }
            }
        }

        return $qb;
    }

    protected function createSearchBy(
        string $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): QueryBuilder {
        $qb = parent::createSearchBy($pattern, $qb, $criteria, $alias);
        $this->alterQueryBuilderWithAuthorizationChecker($qb, $alias);

        return $qb;
    }

    public function searchBy(
        string $pattern,
        array $criteria = [],
        array $orders = [],
        ?int $limit = null,
        ?int $offset = null,
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): array {
        return parent::searchBy($pattern, $criteria, $orders, $limit, $offset, static::NODESSOURCES_ALIAS);
    }

    /**
     * @deprecated Use findByNodesSourcesAndFieldNameAndTranslation instead
     */
    public function findByNodesSourcesAndFieldAndTranslation(
        NodesSources $nodesSources,
        NodeTypeFieldInterface $field,
    ): ?array {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $qb->select('ns, n, ua')
            ->innerJoin('ns.node', static::NODE_ALIAS)
            ->leftJoin('ns.urlAliases', 'ua')
            ->innerJoin('n.aNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('fieldName', $field->getName())
            ->setParameter('nodeA', $nodesSources->getNode())
            ->setParameter('translation', $nodesSources->getTranslation());

        return $qb->getQuery()->getResult();
    }

    public function findByNodesSourcesAndFieldNameAndTranslation(
        NodesSources $nodesSources,
        string $fieldName,
    ): ?array {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $qb->select('ns, n, ua')
            ->innerJoin('ns.node', static::NODE_ALIAS)
            ->leftJoin('ns.urlAliases', 'ua')
            ->innerJoin('n.aNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('fieldName', $fieldName)
            ->setParameter('nodeA', $nodesSources->getNode())
            ->setParameter('translation', $nodesSources->getTranslation());

        return $qb->getQuery()->getResult();
    }

    public function findByNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $qb->select('ns, n, ua')
            ->innerJoin('ns.node', static::NODE_ALIAS)
            ->innerJoin('ns.translation', static::TRANSLATION_ALIAS)
            ->leftJoin('ns.urlAliases', 'ua')
            ->andWhere($qb->expr()->eq('n.id', ':node'))
            ->addOrderBy(static::TRANSLATION_ALIAS.'.defaultTranslation', 'DESC')
            ->addOrderBy(static::TRANSLATION_ALIAS.'.locale', 'ASC')
            ->setParameter('node', $node)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);
        if (!$this->previewResolver->isPreview()) {
            $qb->andWhere($qb->expr()->eq(static::TRANSLATION_ALIAS.'.available', ':available'))
                ->setParameter('available', true);
        }

        return $qb->getQuery()->getResult();
    }

    protected function classicLikeComparison(
        string $pattern,
        QueryBuilder $qb,
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): QueryBuilder {
        $qb = parent::classicLikeComparison($pattern, $qb, $alias);
        $qb
            ->innerJoin($alias.'.node', static::NODE_ALIAS)
            ->leftJoin(static::NODE_ALIAS.'.attributeValues', 'av')
            ->leftJoin('av.attributeValueTranslations', 'avt')
        ;
        $value = '%'.strip_tags(\mb_strtolower($pattern)).'%';
        $qb->orWhere($qb->expr()->like('LOWER(avt.value)', $qb->expr()->literal($value)));
        $qb->orWhere($qb->expr()->like('LOWER('.static::NODE_ALIAS.'.nodeName)', $qb->expr()->literal($value)));

        return $qb;
    }

    /**
     * Get every nodeSources parents from direct parent to farthest ancestor.
     *
     * @return array<NodesSources>
     *
     * @throws NonUniqueResultException
     */
    public function findParents(
        NodesSources $nodeSource,
        ?array $criteria = null,
    ): array {
        $parentsNodeSources = [];

        if (null === $criteria) {
            $criteria = [];
        }

        $parent = $nodeSource;

        while (null !== $parent) {
            $criteria = array_merge(
                $criteria,
                [
                    'node' => $parent->getNode()->getParent(),
                    'translation' => $nodeSource->getTranslation(),
                ]
            );
            $currentParent = $this->findOneBy(
                $criteria,
                []
            );

            if (null !== $currentParent) {
                $parentsNodeSources[] = $currentParent;
            }

            $parent = $currentParent;
        }

        return $parentsNodeSources;
    }

    /**
     * Get children nodes sources to lock with current translation.
     *
     * @param array|null $criteria Additional criteria
     * @param array|null $order    Non default ordering
     *
     * @return Paginator<NodesSources>|array<NodesSources>
     */
    public function findChildren(
        NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): Paginator|array {
        $defaultCriteria = [
            'node.parent' => $nodeSource->getNode(),
            'translation' => $nodeSource->getTranslation(),
        ];

        if (null !== $order) {
            $defaultOrder = $order;
        } else {
            $defaultOrder = [
                'node.position' => 'ASC',
            ];
        }

        if (null !== $criteria) {
            $defaultCriteria = array_merge($defaultCriteria, $criteria);
        }

        return $this->findBy(
            $defaultCriteria,
            $defaultOrder
        );
    }

    /**
     * Get first node-source among current node-source children.
     *
     * @throws NonUniqueResultException
     */
    public function findFirstChild(
        ?NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        $defaultCriteria = [
            'node.parent' => $nodeSource?->getNode() ?? null,
        ];

        if (null !== $nodeSource) {
            $defaultCriteria['translation'] = $nodeSource->getTranslation();
        }

        if (null !== $order) {
            $defaultOrder = $order;
        } else {
            $defaultOrder = [
                'node.position' => 'ASC',
            ];
        }

        if (null !== $criteria) {
            $defaultCriteria = array_merge($defaultCriteria, $criteria);
        }

        return $this->findOneBy(
            $defaultCriteria,
            $defaultOrder
        );
    }

    /**
     * Get last node-source among current node-source children.
     *
     * @throws NonUniqueResultException
     */
    public function findLastChild(
        ?NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        $defaultCriteria = [
            'node.parent' => $nodeSource?->getNode() ?? null,
        ];

        if (null !== $nodeSource) {
            $defaultCriteria['translation'] = $nodeSource->getTranslation();
        }

        if (null !== $order) {
            $defaultOrder = $order;
        } else {
            $defaultOrder = [
                'node.position' => 'DESC',
            ];
        }

        if (null !== $criteria) {
            $defaultCriteria = array_merge($defaultCriteria, $criteria);
        }

        return $this->findOneBy(
            $defaultCriteria,
            $defaultOrder
        );
    }

    /**
     * Get first node-source in the same parent as current node-source.
     *
     * @throws NonUniqueResultException
     */
    public function findFirstSibling(
        NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        if (null !== $nodeSource->getParent()) {
            return $this->findFirstChild($nodeSource->getParent(), $criteria, $order);
        }

        return $this->findFirstChild(null, $criteria, $order);
    }

    /**
     * Get last node-source in the same parent as current node-source.
     *
     * @throws NonUniqueResultException
     */
    public function findLastSibling(
        NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        if (null !== $nodeSource->getParent()) {
            return $this->findLastChild($nodeSource->getParent(), $criteria, $order);
        }

        return $this->findLastChild(null, $criteria, $order);
    }

    /**
     * Get previous node-source from hierarchy.
     *
     * @throws NonUniqueResultException
     */
    public function findPrevious(
        NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        if ($nodeSource->getNode()->getPosition() <= 1) {
            return null;
        }

        $defaultCriteria = [
            /*
             * Use < operator to get first next nodeSource
             * even if it’s not the next position index
             */
            'node.position' => [
                '<',
                $nodeSource
                    ->getNode()
                    ->getPosition(),
            ],
            'node.parent' => $nodeSource->getNode()->getParent(),
            'translation' => $nodeSource->getTranslation(),
        ];
        if (null !== $criteria) {
            $defaultCriteria = array_merge($defaultCriteria, $criteria);
        }

        if (null === $order) {
            $order = [];
        }

        $order['node.position'] = 'DESC';

        return $this->findOneBy(
            $defaultCriteria,
            $order
        );
    }

    /**
     * Get next node-source from hierarchy.
     *
     * @throws NonUniqueResultException
     */
    public function findNext(
        NodesSources $nodeSource,
        ?array $criteria = null,
        ?array $order = null,
    ): ?NodesSources {
        $defaultCriteria = [
            /*
             * Use > operator to get first next nodeSource
             * even if it’s not the next position index
             */
            'node.position' => [
                '>',
                $nodeSource
                    ->getNode()
                    ->getPosition(),
            ],
            'node.parent' => $nodeSource->getNode()->getParent(),
            'translation' => $nodeSource->getTranslation(),
        ];
        if (null !== $criteria) {
            $defaultCriteria = array_merge($defaultCriteria, $criteria);
        }

        if (null === $order) {
            $order = [];
        }

        $order['node.position'] = 'ASC';

        return $this->findOneBy(
            $defaultCriteria,
            $order
        );
    }
}
