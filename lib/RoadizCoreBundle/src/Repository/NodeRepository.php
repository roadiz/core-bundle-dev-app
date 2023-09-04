<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Component\Security\Core\Security;
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
        Security $security
    ) {
        parent::__construct($registry, Node::class, $previewResolver, $dispatcher, $security);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $property
     * @param mixed $value
     *
     * @return Event
     */
    protected function dispatchQueryBuilderBuildEvent(QueryBuilder $qb, string $property, mixed $value): object
    {
        // @phpstan-ignore-next-line
        return $this->dispatcher->dispatch(
            new QueryBuilderBuildEvent($qb, Node::class, $property, $value, $this->getEntityName())
        );
    }

    /**
     * @param QueryBuilder $qb
     * @param string $property
     * @param mixed $value
     *
     * @return Event
     */
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
     * @param TranslationInterface|null $translation
     *
     * @return int
     * @throws NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function countBy(
        mixed $criteria,
        TranslationInterface $translation = null
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
     *
     * @param array            $criteria
     * @param QueryBuilder     $qb
     * @param TranslationInterface|null $translation
     */
    protected function filterByTranslation(
        array $criteria,
        QueryBuilder $qb,
        TranslationInterface $translation = null
    ): void {
        if (
            isset($criteria['translation']) ||
            isset($criteria['translation.locale']) ||
            isset($criteria['translation.id']) ||
            isset($criteria['translation.available'])
        ) {
            $qb->innerJoin(self::NODE_ALIAS . '.nodeSources', self::NODESSOURCES_ALIAS);
            $qb->innerJoin(self::NODESSOURCES_ALIAS . '.translation', self::TRANSLATION_ALIAS);
        } else {
            if (null !== $translation) {
                /*
                 * With a given translation
                 */
                $qb->innerJoin(
                    'n.nodeSources',
                    self::NODESSOURCES_ALIAS,
                    'WITH',
                    self::NODESSOURCES_ALIAS . '.translation = :translation'
                );
            } else {
                /*
                 * With a null translation, not filter by translation to enable
                 * nodes with only one translation which is not the default one.
                 */
                $qb->innerJoin(self::NODE_ALIAS . '.nodeSources', self::NODESSOURCES_ALIAS);
            }
        }
    }

    /**
     * Add a tag filtering to queryBuilder.
     *
     * @param array        $criteria
     * @param QueryBuilder $qb
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
     *
     * @param array        $criteria
     * @param QueryBuilder $qb
     */
    protected function filterByCriteria(array &$criteria, QueryBuilder $qb): void
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        /*
         * Reimplementing findBy features…
         */
        foreach ($criteria as $key => $value) {
            if ($key == "tags" || $key == "tagExclusive") {
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
                $prefix = self::NODE_ALIAS . '.';
                // Dots are forbidden in field definitions
                $baseKey = $simpleQB->getParameterKey($key);
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $prefix, $key, $baseKey));
            }
        }
    }

    /**
     * Bind parameters to generated query.
     *
     * @param array $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByCriteria(array &$criteria, QueryBuilder $qb): void
    {
        /*
         * Reimplementing findBy features…
         */
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            if ($key == "tags" || $key == "tagExclusive") {
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
     *
     * @param QueryBuilder $qb
     * @param TranslationInterface|null $translation
     */
    protected function applyTranslationByTag(
        QueryBuilder $qb,
        TranslationInterface $translation = null
    ): void {
        if (null !== $translation) {
            $qb->setParameter('translation', $translation);
        }
    }

    /**
     * Just like the findBy method but with a given Translation
     *
     * If no translation nor authorizationChecker is given, the vanilla `findBy`
     * method will be called instead.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param TranslationInterface|null $translation
     * @return array|Paginator
     */
    public function findByWithTranslation(
        array $criteria,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        TranslationInterface $translation = null
    ): array|Paginator {
        return $this->findBy(
            $criteria,
            $orderBy,
            $limit,
            $offset,
            $translation
        );
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
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param TranslationInterface|null $translation
     * @return array|Paginator
     */
    public function findBy(
        array $criteria,
        array $orderBy = null,
        $limit = null,
        $offset = null,
        TranslationInterface $translation = null
    ): array|Paginator {
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
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        if (
            null !== $limit &&
            null !== $offset
        ) {
            /*
             * We need to use Doctrine paginator
             * if a limit is set because of the default inner join
             */
            return new Paginator($query);
        } else {
            return $query->getResult();
        }
    }

    /**
     * Create a secureTranslationInterface query with node.published = true if user is
     * not a Backend user and if authorizationChecker is defined.
     *
     * This method allows to pre-filter Nodes with a given translation.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param TranslationInterface|null $translation
     * @return QueryBuilder
     */
    protected function getContextualQueryWithTranslation(
        array &$criteria,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        TranslationInterface $translation = null
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
                if (str_starts_with($key, self::NODESSOURCES_ALIAS . '.')) {
                    $qb->addOrderBy($key, $value);
                } elseif (str_starts_with($key, self::NODETYPE_ALIAS . '.')) {
                    if (!$this->hasJoinedNodeType($qb, self::NODE_ALIAS)) {
                        $qb->innerJoin(self::NODE_ALIAS . '.nodeType', self::NODETYPE_ALIAS);
                    }
                    $qb->addOrderBy($key, $value);
                } else {
                    $qb->addOrderBy(self::NODE_ALIAS . '.' . $key, $value);
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
     * @param array $criteria
     * @param TranslationInterface|null $translation
     * @return null|Node
     * @throws NonUniqueResultException
     */
    public function findOneByWithTranslation(
        array $criteria,
        TranslationInterface $translation = null
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
     * @param array $criteria
     * @param array|null $orderBy
     * @param TranslationInterface|null $translation
     * @return null|Node
     * @throws NonUniqueResultException
     */
    public function findOneBy(
        array $criteria,
        array $orderBy = null,
        TranslationInterface $translation = null
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
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getOneOrNullResult();
    }

    /**
     * Find one Node with its Id and a given translation.
     *
     * @param int $nodeId
     * @param TranslationInterface $translation
     * @return null|Node
     * @throws NonUniqueResultException
     */
    public function findWithTranslation(
        int $nodeId,
        TranslationInterface $translation
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
     * @param int $nodeId
     * @return null|Node
     * @throws NonUniqueResultException
     */
    public function findWithDefaultTranslation(
        int $nodeId
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
     * @param string $nodeName
     * @param TranslationInterface $translation
     *
     * @return null|Node
     * @throws NonUniqueResultException
     * @deprecated Use findNodeTypeNameAndSourceIdByIdentifier
     */
    public function findByNodeNameWithTranslation(
        string $nodeName,
        TranslationInterface $translation
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

    /**
     * Find one node using its nodeName and a translation, or a unique URL alias.
     *
     * @param string $identifier
     * @param TranslationInterface|null $translation
     * @param bool $availableTranslation
     * @param bool $allowNonReachableNodes
     * @return array|null Array with node-type "name" and node-source "id"
     * @throws NonUniqueResultException
     */
    public function findNodeTypeNameAndSourceIdByIdentifier(
        string $identifier,
        ?TranslationInterface $translation,
        bool $availableTranslation = false,
        bool $allowNonReachableNodes = true
    ): ?array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('nt.name, ns.id')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('n.nodeType', self::NODETYPE_ALIAS)
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->leftJoin('ns.urlAliases', 'uas')
            ->andWhere($qb->expr()->orX(
                $qb->expr()->eq('uas.alias', ':identifier'),
                $qb->expr()->andX(
                    $qb->expr()->eq('n.nodeName', ':identifier'),
                    $qb->expr()->eq('t.id', ':translation')
                )
            ))
            ->setParameter('identifier', $identifier)
            ->setParameter('translation', $translation)
            ->setMaxResults(1)
            ->setCacheable(true);

        if (!$allowNonReachableNodes) {
            $qb->andWhere($qb->expr()->eq('nt.reachable', ':reachable'))
                ->setParameter('reachable', true);
        }

        if ($availableTranslation) {
            $qb->andWhere($qb->expr()->eq('t.available', ':available'))
                ->setParameter('available', true);
        }

        $this->alterQueryBuilderWithAuthorizationChecker($qb);
        $query = $qb->getQuery();
        $query->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
        $query->setHydrationMode(Query::HYDRATE_ARRAY);
        return $query->getOneOrNullResult();
    }

    /**
     * Find one Node with its nodeName and the default translation.
     *
     * @param string $nodeName
     *
     * @return null|Node
     * @throws NonUniqueResultException
     * @deprecated Use findNodeTypeNameAndSourceIdByIdentifier
     */
    public function findByNodeNameWithDefaultTranslation(
        string $nodeName
    ): ?Node {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('n.nodeName', ':nodeName'))
            ->andWhere($qb->expr()->eq('t.defaultTranslation', ':defaultTranslation'))
            ->setMaxResults(1)
            ->setParameter('nodeName', $nodeName)
            ->setParameter('defaultTranslation', true)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Find the Home node with a given translation.
     *
     * @param TranslationInterface|null $translation
     * @return null|Node
     * @throws NonUniqueResultException
     */
    public function findHomeWithTranslation(
        TranslationInterface $translation = null
    ): ?Node {
        if (null === $translation) {
            return $this->findHomeWithDefaultTranslation();
        }

        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('n.home', ':home'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->setMaxResults(1)
            ->setParameter('home', true)
            ->setParameter('translation', $translation)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * Find the Home node with the default translation.
     *
     * @return null|Node
     * @throws NonUniqueResultException
     */
    public function findHomeWithDefaultTranslation(): ?Node
    {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('n.home', ':home'))
            ->andWhere($qb->expr()->eq('t.defaultTranslation', ':defaultTranslation'))
            ->setMaxResults(1)
            ->setParameter('home', true)
            ->setParameter('defaultTranslation', true)
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param TranslationInterface $translation
     * @param Node|null $parent
     * @return array
     */
    public function findByParentWithTranslation(
        TranslationInterface $translation,
        Node $parent = null
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns, ua')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->leftJoin(self::NODESSOURCES_ALIAS . '.urlAliases', 'ua')
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->setParameter('translation', $translation)
            ->addOrderBy('n.position', 'ASC')
            ->setCacheable(true);

        if ($parent === null) {
            $qb->andWhere($qb->expr()->isNull('n.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('n.parent', ':parent'))
                ->setParameter('parent', $parent);
        }

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Node|null $parent
     * @return Node[]
     */
    public function findByParentWithDefaultTranslation(Node $parent = null): array
    {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->innerJoin('ns.translation', self::TRANSLATION_ALIAS)
            ->andWhere($qb->expr()->eq('t.defaultTranslation', true))
            ->addOrderBy('n.position', 'ASC')
            ->setCacheable(true);

        if ($parent === null) {
            $qb->andWhere($qb->expr()->isNull('n.parent'));
        } else {
            $qb->andWhere($qb->expr()->eq('n.parent', ':parent'))
                ->setParameter('parent', $parent);
        }

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $urlAliasAlias
     *
     * @return null|Node
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
     * @return null|Node
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
     * @return bool
     * @throws NonUniqueResultException|\Doctrine\ORM\NoResultException
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
     * @param Node $node
     * @param NodeTypeFieldInterface $field
     * @return Node[]
     */
    public function findByNodeAndField(
        Node $node,
        NodeTypeFieldInterface $field
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select(self::NODE_ALIAS)
            ->innerJoin('n.aNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.field', ':field'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('field', $field)
            ->setParameter('nodeA', $node);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Node $node
     * @param NodeTypeFieldInterface $field
     * @param TranslationInterface $translation
     * @return array
     */
    public function findByNodeAndFieldAndTranslation(
        Node $node,
        NodeTypeFieldInterface $field,
        TranslationInterface $translation
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.aNodes', 'ntn')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('ntn.field', ':field'))
            ->andWhere($qb->expr()->eq('ntn.nodeA', ':nodeA'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('field', $field)
            ->setParameter('nodeA', $node)
            ->setParameter('translation', $translation);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Node $node
     * @param NodeTypeFieldInterface $field
     * @return array
     */
    public function findByReverseNodeAndField(
        Node $node,
        NodeTypeFieldInterface $field
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select(self::NODE_ALIAS)
            ->innerJoin('n.bNodes', 'ntn')
            ->andWhere($qb->expr()->eq('ntn.field', ':field'))
            ->andWhere($qb->expr()->eq('ntn.nodeB', ':nodeB'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('field', $field)
            ->setParameter('nodeB', $node);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Node $node
     * @param NodeTypeFieldInterface $field
     * @param TranslationInterface $translation
     * @return array
     */
    public function findByReverseNodeAndFieldAndTranslation(
        Node $node,
        NodeTypeFieldInterface $field,
        TranslationInterface $translation
    ): array {
        $qb = $this->createQueryBuilder(self::NODE_ALIAS);
        $qb->select('n, ns')
            ->innerJoin('n.bNodes', 'ntn')
            ->innerJoin('n.nodeSources', self::NODESSOURCES_ALIAS)
            ->andWhere($qb->expr()->eq('ntn.field', ':field'))
            ->andWhere($qb->expr()->eq('ns.translation', ':translation'))
            ->andWhere($qb->expr()->eq('ntn.nodeB', ':nodeB'))
            ->addOrderBy('ntn.position', 'ASC')
            ->setCacheable(true);

        $this->alterQueryBuilderWithAuthorizationChecker($qb);

        $qb->setParameter('field', $field)
            ->setParameter('translation', $translation)
            ->setParameter('nodeB', $node);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Node $node
     * @return array<int>
     */
    public function findAllOffspringIdByNode(Node $node): array
    {
        $theOffsprings = [];
        $in = [$node->getId()];

        do {
            $theOffsprings = array_merge($theOffsprings, $in);
            $subQb = $this->createQueryBuilder('n');
            $subQb->select('n.id')
                ->andWhere($subQb->expr()->in('n.parent', ':tab'))
                ->setParameter('tab', $in)
                ->setCacheable(true);
            $result = $subQb->getQuery()->getScalarResult();
            $in = [];

            //For memory optimizations
            foreach ($result as $item) {
                $in[] = (int) $item['id'];
            }
        } while (!empty($in));
        return $theOffsprings;
    }

    /**
     * Find all node’ parents with criteria and ordering.
     *
     * @param Node $node
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @param TranslationInterface|null $translation
     * @return array|Paginator|null
     */
    public function findAllNodeParentsBy(
        Node $node,
        array $criteria,
        array $orderBy = null,
        ?int $limit = null,
        ?int $offset = null,
        TranslationInterface $translation = null
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

    public function findAllParentsIdByNode(Node $node): array
    {
        $theParents = [];
        $parent = $node->getParent();

        while (null !== $parent) {
            $theParents[] = $parent->getId();
            $parent = $parent->getParent();
        }

        return $theParents;
    }

    /**
     * Create a Criteria object from a search pattern and additional fields.
     *
     * @param string       $pattern  Search pattern
     * @param QueryBuilder $qb       QueryBuilder to pass
     * @param array        $criteria Additional criteria
     * @param string       $alias    SQL query table alias
     *
     * @return QueryBuilder
     */
    protected function createSearchBy(
        string $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        string $alias = "obj"
    ): QueryBuilder {
        $this->classicLikeComparison($pattern, $qb, $alias);

        /*
         * Search in translations
         */
        $qb->innerJoin($alias . '.nodeSources', self::NODESSOURCES_ALIAS);
        $criteriaFields = [];
        foreach (self::getSearchableColumnsNames($this->_em->getClassMetadata(NodesSources::class)) as $field) {
            $criteriaFields[$field] = '%' . strip_tags(mb_strtolower($pattern)) . '%';
        }
        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', self::NODESSOURCES_ALIAS . '.' . $key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }

        /*
         * Handle Tag relational queries
         */
        if (isset($criteria['tags'])) {
            if ($criteria['tags'] instanceof PersistableInterface) {
                $qb->innerJoin(
                    $alias . '.nodesTags',
                    'ntg',
                    Expr\Join::WITH,
                    $qb->expr()->eq('ntg.tag', (int) $criteria['tags']->getId())
                );
            } elseif (is_array($criteria['tags'])) {
                $qb->innerJoin(
                    $alias . '.nodesTags',
                    'ntg',
                    Expr\Join::WITH,
                    $qb->expr()->in('ntg.tag', $criteria['tags'])
                );
            } elseif (is_integer($criteria['tags'])) {
                $qb->innerJoin(
                    $alias . '.nodesTags',
                    'ntg',
                    Expr\Join::WITH,
                    $qb->expr()->eq('ntg.tag', (int) $criteria['tags'])
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
                if ($key == 'translation') {
                    if (!$this->hasJoinedNodesSources($qb, $alias)) {
                        $qb->innerJoin($alias . '.nodeSources', self::NODESSOURCES_ALIAS);
                    }
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding(
                        $value,
                        self::NODESSOURCES_ALIAS . '.',
                        $key,
                        $baseKey
                    ));
                } else {
                    $qb->andWhere($simpleQB->buildExpressionWithoutBinding(
                        $value,
                        $alias . '.',
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
     * @param Node|null $parent
     * @return int
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findLatestPositionInParent(Node $parent = null): int
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
}
