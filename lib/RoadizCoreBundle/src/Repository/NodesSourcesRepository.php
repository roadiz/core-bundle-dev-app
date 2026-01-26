<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryNodesSourcesEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template T of NodesSources
 *
 * @extends StatusAwareRepository<T|NodesSources>
 *
 * @template-extends StatusAwareRepository<T|NodesSources>
 */
class NodesSourcesRepository extends StatusAwareRepository
{
    /**
     * @param class-string<NodesSources> $entityClass
     */
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
        string $entityClass = NodesSources::class,
    ) {
        parent::__construct($registry, $entityClass, $previewResolver, $dispatcher, $security);
    }

    /**
     * @return Event
     */
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
            $this->joinNodeOnce($qb);
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
    #[\Override]
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

    /**
     * @return T|null
     */
    public function findOneByIdentifierAndTranslation(
        string $identifier,
        ?TranslationInterface $translation,
        bool $availableTranslation = false,
    ): ?NodesSources {
        $qb = $this->createQueryBuilder(self::NODESSOURCES_ALIAS);
        $qb->select([self::NODESSOURCES_ALIAS, static::NODE_ALIAS, 'ua'])
            ->innerJoin(self::NODESSOURCES_ALIAS.'.node', self::NODE_ALIAS)
            ->innerJoin(self::NODESSOURCES_ALIAS.'.translation', self::TRANSLATION_ALIAS)
            ->leftJoin(self::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('ua.alias', ':identifier'),
                $qb->expr()->andX(
                    $qb->expr()->eq(self::NODE_ALIAS.'.nodeName', ':identifier'),
                    $qb->expr()->eq(self::TRANSLATION_ALIAS.'.id', ':translation')
                )
            ))
            ->setParameter('identifier', $identifier)
            ->setParameter('translation', $translation)
            ->setMaxResults(1)
            ->setCacheable(true);

        if ($availableTranslation) {
            $qb->andWhere($qb->expr()->eq(self::TRANSLATION_ALIAS.'.available', ':available'))
                ->setParameter('available', true);
        }

        $this->alterQueryBuilderWithAuthorizationChecker($qb);
        $query = $qb->getQuery();
        $query->setCacheable(true);

        return $query->getOneOrNullResult();
    }

    #[\Override]
    public function alterQueryBuilderWithAuthorizationChecker(
        QueryBuilder $qb,
        string $prefix = EntityRepository::NODESSOURCES_ALIAS,
    ): QueryBuilder {
        if (true === $this->isDisplayingAllNodesStatuses()) {
            return $qb;
        }

        $this->joinNodeOnce($qb, $prefix);

        if (true === $this->isDisplayingNotPublishedNodes() || $this->previewResolver->isPreview()) {
            /*
             * Forbid deleted node for backend user when authorizationChecker not null.
             */
            $qb->andWhere($qb->expr()->lte(static::NODE_ALIAS.'.status', ':node_status'));
            $qb->setParameter('node_status', NodeStatus::PUBLISHED);

            return $qb;
        }

        /*
         * Forbid unpublished node for anonymous and not backend users.
         */
        $qb->andWhere($qb->expr()->lte($prefix.'.publishedAt', ':now'));
        $qb->andWhere($qb->expr()->eq(static::NODE_ALIAS.'.status', ':node_status'));
        $qb->setParameter('node_status', NodeStatus::PUBLISHED);
        $qb->setParameter('now', new \DateTime('now'));

        return $qb;
    }

    public function joinNodeOnce(QueryBuilder $qb, string $prefix = EntityRepository::NODESSOURCES_ALIAS): QueryBuilder
    {
        if (!$this->hasJoinedNode($qb, $prefix)) {
            $qb->innerJoin($prefix.'.node', static::NODE_ALIAS);
        }

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
        $this->joinNodeOnce($qb);
        $this->alterQueryBuilderWithAuthorizationChecker($qb);
        $qb->addSelect(static::NODE_ALIAS);

        /*
         * Filtering by tag
         */
        $this->filterByTag($criteria, $qb);
        $this->filterByCriteria($criteria, $qb);

        // Add ordering
        if (null !== $orderBy) {
            foreach ($orderBy as $key => $value) {
                if (\str_contains((string) $key, 'node.')) {
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
     */
    protected function getCountContextualQuery(array &$criteria): QueryBuilder
    {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $this->alterQueryBuilderWithAuthorizationChecker($qb);
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
    #[\Override]
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
     * * key => array('NOT IN', $array)
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
     * @return array<T>
     */
    #[\Override]
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
        }

        return $query->getResult();
    }

    /**
     * A secure findOneBy with which user must be a backend user
     * to see unpublished nodes.
     *
     * @return T|null
     *
     * @throws NonUniqueResultException
     */
    #[\Override]
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
     * @return T|null
     */
    public function findOneByNodeAndTranslation(Node $node, ?TranslationInterface $translation): ?NodesSources
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
    #[\Override]
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
                if (\str_contains((string) $key, 'node.')) {
                    $this->joinNodeOnce($qb, $alias);
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

    #[\Override]
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

    #[\Override]
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
     * @param array<class-string<NodesSources>> $nodeSourceClasses
     *
     * @return array<T>
     */
    public function findByNodesSourcesAndFieldNameAndTranslation(
        NodesSources $nodesSources,
        string $fieldName,
        array $nodeSourceClasses = [],
    ): array {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $this->joinNodeOnce($qb);
        $qb->innerJoin(static::NODE_ALIAS.'.aNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->andWhere($qb->expr()->eq(static::NODESSOURCES_ALIAS.'.translation', ':translation'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        if (count($nodeSourceClasses) > 0) {
            $qb->andWhere($qb->expr()->orX(
                ...array_map(
                    fn (string $nodeSourceClass) => $qb->expr()->isInstanceOf(static::NODESSOURCES_ALIAS, $nodeSourceClass),
                    $nodeSourceClasses
                )
            ));
        }

        $qb->setParameter('fieldName', $fieldName)
            ->setParameter('nodeA', $nodesSources->getNode())
            ->setParameter('translation', $nodesSources->getTranslation());

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<T>
     */
    public function findByNode(Node $node): array
    {
        $qb = $this->createQueryBuilder(static::NODESSOURCES_ALIAS);
        $this->joinNodeOnce($qb);
        $qb->select([static::NODESSOURCES_ALIAS, static::NODE_ALIAS, 'ua'])
            ->innerJoin(static::NODESSOURCES_ALIAS.'.translation', static::TRANSLATION_ALIAS)
            ->leftJoin(static::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->andWhere($qb->expr()->eq(static::NODE_ALIAS.'.id', ':node'))
            ->addOrderBy(static::TRANSLATION_ALIAS.'.defaultTranslation', 'DESC')
            ->addOrderBy(static::TRANSLATION_ALIAS.'.locale', 'ASC')
            ->setParameter('node', $node)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);
        if (!$this->previewResolver->isPreview() && !$this->isDisplayingAllNodesStatuses()) {
            $qb->andWhere($qb->expr()->eq(static::TRANSLATION_ALIAS.'.available', ':available'))
                ->setParameter('available', true);
        }

        return $qb->getQuery()->getResult();
    }

    #[\Override]
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
