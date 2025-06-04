<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Model\NodeTreeDto;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @extends StatusAwareRepository<Node>
 */
final class NodeRepository extends StatusAwareRepository
{
    public function __construct(
        ManagerRegistry $registry,
        PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        Security $security,
    ) {
        parent::__construct($registry, Node::class, $previewResolver, $dispatcher, $security);
    }

    /**
     * @return Event
     */
    #[\Override]
    protected function dispatchQueryBuilderBuildEvent(QueryBuilder $qb, string $property, mixed $value): object
    {
        // @phpstan-ignore-next-line
        return $this->dispatcher->dispatch(
            new QueryBuilderBuildEvent($qb, Node::class, $property, $value, $this->getEntityName())
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
            new QueryBuilderApplyEvent($qb, Node::class, $property, $value, $this->getEntityName())
        );
    }

    /**
     * Just like the countBy method but with relational criteria.
     *
     * @param Criteria|mixed|array $criteria
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[\Override]
    public function countBy(
        mixed $criteria,
        ?TranslationInterface $translation = null,
    ): int {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select($qb->expr()->countDistinct(self::NODE_ALIAS));
        $qb->setMaxResults(1);

        if (null !== $translation) {
            $this->filterByTranslation($criteria, $qb, $translation);
        }

        /*
         * Filtering by tag
         */
        $this->filterByTag($criteria, $qb);
        $this->filterByCriteria($criteria, $qb);
        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByTag($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $this->applyTranslationByTag($qb, $translation);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create filters according to any translation criteria OR argument.
     */
    protected function filterByTranslation(
        array $criteria,
        QueryBuilder $qb,
        ?TranslationInterface $translation = null,
    ): void {
        if (
            isset($criteria['translation'])
            || isset($criteria['translation.locale'])
            || isset($criteria['translation.id'])
            || isset($criteria['translation.available'])
        ) {
            if (!$this->hasJoinedNodesSources($qb, self::NODE_ALIAS)) {
                $qb->innerJoin(self::NODE_ALIAS.'.nodeSources', self::NODESSOURCES_ALIAS);
            }
            $qb->innerJoin(self::NODESSOURCES_ALIAS.'.translation', self::TRANSLATION_ALIAS);
        } else {
            if (null !== $translation) {
                /*
                 * With a given translation
                 */
                $qb->innerJoin(
                    'n.nodeSources',
                    self::NODESSOURCES_ALIAS,
                    'WITH',
                    self::NODESSOURCES_ALIAS.'.translation = :translation'
                );
            } else {
                /*
                 * With a null translation, not filter by translation to enable
                 * nodes with only one translation which is not the default one.
                 */
                if (!$this->hasJoinedNodesSources($qb, self::NODE_ALIAS)) {
                    $qb->innerJoin(self::NODE_ALIAS.'.nodeSources', self::NODESSOURCES_ALIAS);
                }
            }
        }
    }

    /**
     * Add a tag filtering to queryBuilder.
     */
    protected function filterByTag(array &$criteria, QueryBuilder $qb): void
    {
        if (key_exists('tags', $criteria)) {
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
     * * key => array('NOT IN', $array)
     * * key => 'NOT NULL'
     *
     * You can filter with translations relation, examples:
     *
     * * `translation => $object`
     * * `translation.locale => 'fr_FR'`
     */
    protected function filterByCriteria(array &$criteria, QueryBuilder $qb): void
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        /*
         * Reimplementing findBy features…
         */
        foreach ($criteria as $key => $value) {
            if ('tags' == $key || 'tagExclusive' == $key) {
                continue;
            }
            /*
             * Main QueryBuilder dispatch loop for
             * custom properties criteria.
             */
            $event = $this->dispatchQueryBuilderBuildEvent($qb, $key, $value);

            if (!$event->isPropagationStopped()) {
                /*
                 * compute prefix for
                 * filtering node, and sources relation fields
                 */
                $prefix = self::NODE_ALIAS.'.';
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

            $event = $this->dispatchQueryBuilderApplyEvent($qb, $key, $value);
            if (!$event->isPropagationStopped()) {
                $simpleQB->bindValue($key, $value);
            }
        }
    }

    /**
     * Bind translation parameter to final query.
     */
    protected function applyTranslationByTag(
        QueryBuilder $qb,
        ?TranslationInterface $translation = null,
    ): void {
        if (null !== $translation) {
            $qb->setParameter('translation', $translation);
        }
    }

    /**
     * Just like the findBy method but with relational criteria.
     *
     * Reimplementing findBy features… with extra things:
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
     * You can filter with translations relation, examples:
     *
     * * `translation => $object`
     * * `translation.locale => 'fr_FR'`
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
     * @return array<Node>
     */
    #[\Override]
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
        ?TranslationInterface $translation = null,
    ): array {
        $qb = $this->getContextualQueryWithTranslation(
            $criteria,
            $orderBy,
            $limit,
            $offset,
            $translation
        );

        $qb->setCacheable(true);
        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByTag($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $this->applyTranslationByTag($qb, $translation);
        // @phpstan-ignore-next-line
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
     * @return array<NodeTreeDto>
     */
    public function findByAsNodeTreeDto(
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        ?TranslationInterface $translation = null,
    ): array {
        $qb = $this->getContextualQueryWithTranslation(
            $criteria,
            $orderBy,
            $limit,
            $offset,
            $translation
        );

        $qb->setCacheable(true);
        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByTag($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $this->applyTranslationByTag($qb, $translation);

        $this->alterQueryBuilderAsNodeTreeDto($qb);

        // @phpstan-ignore-next-line
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getResult();
    }

    /**
     * @param string $pattern  Search pattern
     * @param array  $criteria Additional criteria
     *
     * @return array<NodeTreeDto>
     *
     * @psalm-return array<NodeTreeDto>
     */
    public function searchByAsNodeTreeDto(
        string $pattern,
        array $criteria = [],
        array $orders = [],
        ?int $limit = null,
        ?int $offset = null,
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): array {
        $qb = $this->createQueryBuilder($alias);
        $qb = $this->createSearchBy($pattern, $qb, $criteria, $alias);

        // Add ordering
        foreach ($orders as $key => $value) {
            if (
                (\str_starts_with($key, 'node.') || \str_starts_with($key, static::NODE_ALIAS.'.'))
                && $this->hasJoinedNode($qb, $alias)
            ) {
                $key = preg_replace('#^node\.#', static::NODE_ALIAS.'.', $key);
                $qb->addOrderBy($key, $value);
            } elseif (
                \str_starts_with($key, static::NODESSOURCES_ALIAS.'.')
                && $this->hasJoinedNodesSources($qb, $alias)
            ) {
                $qb->addOrderBy($key, $value);
            } else {
                $qb->addOrderBy($alias.'.'.$key, $value);
            }
        }
        if (null !== $offset) {
            $qb->setFirstResult($offset);
        }
        if (null !== $limit) {
            $qb->setMaxResults($limit);
        }

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByCriteria($criteria, $qb);

        $this->alterQueryBuilderAsNodeTreeDto($qb, $alias);

        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getResult();
    }

    protected function alterQueryBuilderAsNodeTreeDto(QueryBuilder $qb, string $alias = self::NODE_ALIAS): QueryBuilder
    {
        if (!$this->hasJoinedNodesSources($qb, $alias)) {
            $qb->innerJoin($alias.'.nodeSources', self::NODESSOURCES_ALIAS);
        }

        $qb->select(sprintf(
            <<<EOT
NEW %s(
    %s.id,
    %s.nodeName,
    %s.hideChildren,
    %s.home,
    %s.visible,
    %s.status,
    IDENTITY(%s.parent),
    %s.childrenOrder,
    %s.childrenOrderDirection,
    %s.locked,
    %s.nodeTypeName,
    %s.id,
    %s.title,
    %s.publishedAt
)
EOT,
            NodeTreeDto::class,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            $alias,
            self::NODESSOURCES_ALIAS,
            self::NODESSOURCES_ALIAS,
            self::NODESSOURCES_ALIAS,
        ));

        return $qb;
    }

    /**
     * Create a secureTranslationInterface query with node.published = true if user is
     * not a Backend user and if authorizationChecker is defined.
     *
     * This method allows to pre-filter Nodes with a given translation.
     */
    protected function getContextualQueryWithTranslation(
        array &$criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        ?TranslationInterface $translation = null,
    ): QueryBuilder {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->addSelect(self::NODESSOURCES_ALIAS);
        $this->filterByTranslation($criteria, $qb, $translation);

        /*
         * Filtering by tag
         */
        $this->filterByTag($criteria, $qb);
        $this->filterByCriteria($criteria, $qb);
        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        // Add ordering
        if (null !== $orderBy) {
            foreach ($orderBy as $key => $value) {
                if (str_starts_with($key, self::NODESSOURCES_ALIAS.'.')) {
                    $qb->addOrderBy($key, $value);
                } else {
                    $qb->addOrderBy(self::NODE_ALIAS.'.'.$key, $value);
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
     * Just like the findOneBy method but with a given Translation and optional
     * AuthorizationChecker.
     *
     * If no translation nor authorizationChecker is given, the vanilla `findOneBy`
     * method will be called instead.
     *
     * @throws NonUniqueResultException
     */
    public function findOneByWithTranslation(
        array $criteria,
        ?TranslationInterface $translation = null,
    ): ?Node {
        return $this->findOneBy(
            $criteria,
            null,
            $translation
        );
    }

    /**
     * Just like the findOneBy method but with relational criteria.
     *
     * @throws NonUniqueResultException
     */
    #[\Override]
    public function findOneBy(
        array $criteria,
        ?array $orderBy = null,
        ?TranslationInterface $translation = null,
    ): ?Node {
        $qb = $this->getContextualQueryWithTranslation(
            $criteria,
            $orderBy,
            1,
            0,
            $translation
        );

        $qb->setCacheable(true);
        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByTag($criteria, $qb);
        $this->applyFilterByCriteria($criteria, $qb);
        $this->applyTranslationByTag($qb, $translation);
        // @phpstan-ignore-next-line
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getOneOrNullResult();
    }

    /**
     * Find one Node with its Id and a given translation.
     *
     * @throws NonUniqueResultException
     */
    public function findWithTranslation(
        int $nodeId,
        TranslationInterface $translation,
    ): ?Node {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('n.id', ':nodeId'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->setMaxResults(1)
            ->setParameter('nodeId', (int) $nodeId)
            ->setParameter('translation', $translation)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Find one Node with its Id and the default translation.
     *
     * @throws NonUniqueResultException
     */
    public function findWithDefaultTranslation(
        int $nodeId,
    ): ?Node {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('n.id', ':nodeId'))
            ->andWhere($qb->expr()->eq('t.defaultTranslation', ':defaultTranslation'))
            ->setMaxResults(1)
            ->setParameter('nodeId', (int) $nodeId)
            ->setParameter('defaultTranslation', true)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Find one Node with its nodeName and a given translation.
     *
     * @throws NonUniqueResultException
     *
     * @deprecated Use findNodeTypeNameAndSourceIdByIdentifier
     */
    public function findByNodeNameWithTranslation(
        string $nodeName,
        TranslationInterface $translation,
    ): ?Node {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('n.nodeName', ':nodeName'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->setMaxResults(1)
            ->setParameter('nodeName', $nodeName)
            ->setParameter('translation', $translation)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findByParentWithTranslation(
        TranslationInterface $translation,
        ?Node $parent = null,
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns, ua')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->leftJoin(self::NODESSOURCES_ALIAS.'.urlAliases', 'ua')
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->setParameter('translation', $translation)
            ->addOrderBy('n.position', 'ASC')
            ->setCacheable(true);

        if (null === $parent) {
            $qb->andWhere($qb->expr()->isNull('n.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('n.parent', ':parent'))
                ->setParameter('parent', $parent);
        }

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return Node[]
     */
    public function findByParentWithDefaultTranslation(?Node $parent = null): array
    {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('t.defaultTranslation', true))
            ->addOrderBy('n.position', 'ASC')
            ->setCacheable(true);

        if (null === $parent) {
            $qb->andWhere($qb->expr()->isNull('n.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('n.parent', ':parent'))
                ->setParameter('parent', $parent);
        }

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findOneWithAliasAndAvailableTranslation(string $urlAliasAlias): ?Node
    {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns, t, uas')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.urlAliases', 'uas')
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('uas.alias', ':alias'))
            ->andWhere($qb->expr()->eq('t.available', ':available'))
            ->setParameter('alias', $urlAliasAlias)
            ->setParameter('available', true)
            ->setMaxResults(1)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $urlAliasAlias
     *
     * @throws NonUniqueResultException
     */
    public function findOneWithAlias($urlAliasAlias): ?Node
    {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns, t, uas')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.urlAliases', 'uas')
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('uas.alias', ':alias'))
            ->setParameter('alias', $urlAliasAlias)
            ->setMaxResults(1)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $nodeName
     *
     * @throws NonUniqueResultException|NoResultException
     */
    public function exists($nodeName): bool
    {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select($qb->expr()->countDistinct('n.nodeName'))
            ->andWhere($qb->expr()->eq('n.nodeName', ':nodeName'))
            ->setParameter('nodeName', $nodeName)
            ->setMaxResults(1)
        ;

        return (bool) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @return Node[]
     */
    public function findByNodeAndField(
        Node $node,
        NodeTypeFieldInterface $field,
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select(self::NODE_ALIAS)
            ->innerJoin('n.aNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('fieldName', $field->getName())
            ->setParameter('nodeA', $node);

        return $qb->getQuery()->getResult();
    }

    public function findByNodeAndFieldAndTranslation(
        Node $node,
        NodeTypeFieldInterface $field,
        TranslationInterface $translation,
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.aNodes', 'ntn')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('fieldName', $field->getName())
            ->setParameter('nodeA', $node)
            ->setParameter('translation', $translation);

        return $qb->getQuery()->getResult();
    }

    public function findByReverseNodeAndField(
        Node $node,
        NodeTypeFieldInterface $field,
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select(self::NODE_ALIAS)
            ->innerJoin('n.bNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ntn.nodeB', ':nodeB'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('fieldName', $field->getName())
            ->setParameter('nodeB', $node);

        return $qb->getQuery()->getResult();
    }

    public function findByReverseNodeAndFieldAndTranslation(
        Node $node,
        NodeTypeFieldInterface $field,
        TranslationInterface $translation,
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.bNodes', 'ntn')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('ntn.fieldName', ':fieldName'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->andWhere($qb->expr()->eq('ntn.nodeB', ':nodeB'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('fieldName', $field->getName())
            ->setParameter('translation', $translation)
            ->setParameter('nodeB', $node);

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int>
     *
     * @internal Use NodeOffspringResolverInterface service instead
     */
    public function findAllOffspringIdByNode(NodeInterface $node): array
    {
        $theOffsprings = [];
        $in = \array_filter([(int) $node->getId()]);

        do {
            $theOffsprings = array_merge($theOffsprings, $in);
            $subQb = $this->createQueryBuilder('n');
            $subQb->select('n.id')
                ->andWhere($subQb->expr()->in('n.parent', ':tab'))
                ->setParameter('tab', $in)
                ->setCacheable(true);
            $result = $subQb->getQuery()->getScalarResult();
            $in = [];

            // For memory optimizations
            foreach ($result as $item) {
                $in[] = (int) $item['id'];
            }
        } while (!empty($in));

        return $theOffsprings;
    }

    /**
     * Find all node’ parents with criteria and ordering.
     */
    public function findAllNodeParentsBy(
        Node $node,
        array $criteria,
        ?array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        ?TranslationInterface $translation = null,
    ): array|Paginator|null {
        $parentsId = $this->findAllParentsIdByNode($node);
        if (count($parentsId) > 0) {
            $criteria['id'] = $parentsId;
        } else {
            return null;
        }

        return $this->findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset,
            $translation
        );
    }

    /**
     * @return array<int|string>
     */
    public function findAllParentsIdByNode(Node $node): array
    {
        $theParents = [];
        $parent = $node->getParent();

        while (null !== $parent) {
            $theParents[] = $parent->getId();
            $parent = $parent->getParent();
        }

        return array_filter($theParents);
    }

    /**
     * Create a Criteria object from a search pattern and additional fields.
     *
     * @param string       $pattern  Search pattern
     * @param QueryBuilder $qb       QueryBuilder to pass
     * @param array        $criteria Additional criteria
     * @param string       $alias    SQL query table alias
     */
    #[\Override]
    protected function createSearchBy(
        string $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        string $alias = 'obj',
    ): QueryBuilder {
        $this->classicLikeComparison($pattern, $qb, $alias);

        /*
         * Search in translations
         */
        $qb->innerJoin($alias.'.nodeSources', self::NODESSOURCES_ALIAS);
        $criteriaFields = [];
        foreach (self::getSearchableColumnsNames($this->_em->getClassMetadata(NodesSources::class)) as $field) {
            $criteriaFields[$field] = '%'.strip_tags(\mb_strtolower($pattern)).'%';
        }
        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', self::NODESSOURCES_ALIAS.'.'.$key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }

        /*
         * Handle Tag relational queries
         */
        if (isset($criteria['tags'])) {
            if ($criteria['tags'] instanceof PersistableInterface) {
                $qb->innerJoin(
                    $alias.'.nodesTags',
                    'ntg',
                    Expr\Join::WITH,
                    $qb->expr()->eq('ntg.tag', $criteria['tags']->getId())
                );
            } elseif (is_array($criteria['tags'])) {
                $qb->innerJoin(
                    $alias.'.nodesTags',
                    'ntg',
                    Expr\Join::WITH,
                    $qb->expr()->in('ntg.tag', $criteria['tags'])
                );
            } elseif (is_integer($criteria['tags'])) {
                $qb->innerJoin(
                    $alias.'.nodesTags',
                    'ntg',
                    Expr\Join::WITH,
                    $qb->expr()->eq('ntg.tag', $criteria['tags'])
                );
            }
            unset($criteria['tags']);
        }

        $this->prepareComparisons($criteria, $qb, $alias);
        /*
         * Alter at the end not to filter in OR groups
         */
        $this->alterQueryBuilderWithAuthorizationChecker($qb, $alias);

        return $qb;
    }

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
                if ('translation' == $key) {
                    if (!$this->hasJoinedNodesSources($qb, $alias)) {
                        $qb->innerJoin($alias.'.nodeSources', self::NODESSOURCES_ALIAS);
                    }
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding(
                        $value,
                        self::NODESSOURCES_ALIAS.'.',
                        $key,
                        $baseKey
                    ));
                } else {
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding(
                        $value,
                        $alias.'.',
                        $key,
                        $baseKey
                    ));
                }
            }
        }

        return $qb;
    }

    /**
     * Get latest position in parent.
     *
     * Parent can be null for node root
     *
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findLatestPositionInParent(?Node $parent = null): int
    {
        $qb = $this->createQueryBuilder('n');
        $qb->select($qb->expr()->max('n.position'));

        if (null !== $parent) {
            $qb->andWhere($qb->expr()->eq('n.parent', ':parent'))
                ->setParameter(':parent', $parent);
        } else {
            $qb->andWhere($qb->expr()->isNull('n.parent'));
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Use by UniqueEntity Validator to bypass node status query filtering.
     *
     * @throws NonUniqueResultException
     */
    public function findOneWithoutSecurity(array $criteria): ?Node
    {
        $this->setDisplayingAllNodesStatuses(true);
        if (1 === count($criteria) && !empty($criteria['nodeName'])) {
            /*
             * Test if nodeName is used as an url-alias too
             */
            $nodeName = (new AsciiSlugger())->slug($criteria['nodeName'])->lower()->trim()->toString();

            $qb = $this->createQueryBuilder('o');
            $qb->leftJoin('o.nodeSources', 'ns')
                ->leftJoin('ns.urlAliases', 'ua')
                ->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('ua.alias', ':nodeName'),
                    $qb->expr()->eq('o.nodeName', ':nodeName')
                ))
                ->setParameter('nodeName', $nodeName)
                ->setMaxResults(1)
                ->setCacheable(true);

            return $qb->getQuery()->getOneOrNullResult();
        }

        return $this->findOneBy($criteria);
    }

    #[\Override]
    protected function classicLikeComparison(
        string $pattern,
        QueryBuilder $qb,
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): QueryBuilder {
        $qb = parent::classicLikeComparison($pattern, $qb, $alias);
        $qb
            ->leftJoin($alias.'.attributeValues', 'av')
            ->leftJoin('av.attributeValueTranslations', 'avt')
        ;
        $value = '%'.strip_tags(\mb_strtolower($pattern)).'%';
        $qb->orWhere($qb->expr()->like('LOWER(avt.value)', $qb->expr()->literal($value)));
        $qb->orWhere($qb->expr()->like('LOWER('.$alias.'.nodeName)', $qb->expr()->literal($value)));

        return $qb;
    }

    /**
     * Get previous node from hierarchy.
     */
    public function findPreviousNode(
        Node $node,
        ?array $criteria = null,
        ?array $order = null,
    ): ?Node {
        if ($node->getPosition() <= 1) {
            return null;
        }
        if (null === $order) {
            $order = [];
        }

        if (null === $criteria) {
            $criteria = [];
        }

        $criteria['parent'] = $node->getParent();
        /*
         * Use < operator to get first previous nodeSource
         * even if it’s not the previous position index
         */
        $criteria['position'] = [
            '<',
            $node->getPosition(),
        ];

        $order['position'] = 'DESC';

        return $this->findOneBy(
            $criteria,
            $order
        );
    }

    /**
     * Get next node from hierarchy.
     */
    public function findNextNode(
        Node $node,
        ?array $criteria = null,
        ?array $order = null,
    ): ?Node {
        if (null === $criteria) {
            $criteria = [];
        }
        if (null === $order) {
            $order = [];
        }

        $criteria['parent'] = $node->getParent();

        /*
         * Use > operator to get first next nodeSource
         * even if it’s not the next position index
         */
        $criteria['position'] = [
            '>',
            $node->getPosition(),
        ];
        $order['position'] = 'ASC';

        return $this->findOneBy(
            $criteria,
            $order
        );
    }
}
