<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use LogicException;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderSelectEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template TEntityClass of object
 * @extends \Doctrine\ORM\EntityRepository<TEntityClass>
 */
abstract class EntityRepository extends \Doctrine\ORM\EntityRepository implements ServiceEntityRepositoryInterface
{
    protected EventDispatcherInterface $dispatcher;

    /**
     * @param ManagerRegistry $registry
     * @param class-string<TEntityClass> $entityClass
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(ManagerRegistry $registry, string $entityClass, EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $manager = $registry->getManagerForClass($entityClass);

        if (!($manager instanceof EntityManagerInterface)) {
            throw new LogicException(sprintf(
                'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                $entityClass
            ));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }

    /**
     * Alias for DQL and Query builder representing Node relation.
     */
    public const DEFAULT_ALIAS = 'obj';

    /**
     * Alias for DQL and Query builder representing Node relation.
     */
    public const NODE_ALIAS = 'n';

    /**
     * Alias for DQL and Query builder representing NodesSources relation.
     */
    public const NODESSOURCES_ALIAS = 'ns';

    /**
     * Alias for DQL and Query builder representing Translation relation.
     */
    public const TRANSLATION_ALIAS = 't';

    /**
     * Alias for DQL and Query builder representing Tag relation.
     */
    public const TAG_ALIAS = 'tg';

    /**
     * Alias for DQL and Query builder representing NodeType relation.
     */
    public const NODETYPE_ALIAS = 'nt';

    /**
     * @param QueryBuilder $qb
     * @param class-string $entityClass
     */
    protected function dispatchQueryBuilderEvent(QueryBuilder $qb, string $entityClass): void
    {
        // @phpstan-ignore-next-line
        $this->dispatcher->dispatch(new QueryBuilderSelectEvent($qb, $entityClass));
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
        return $this->dispatcher->dispatch(new QueryBuilderBuildEvent(
            $qb,
            $this->getEntityName(),
            $property,
            $value,
            $this->getEntityName()
        ));
    }

    /**
     * @param Query $query
     *
     * @return Event
     */
    protected function dispatchQueryEvent(Query $query): object
    {
        // @phpstan-ignore-next-line
        return $this->dispatcher->dispatch(new QueryEvent(
            $query,
            $this->getEntityName()
        ));
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
        return $this->dispatcher->dispatch(new QueryBuilderApplyEvent(
            $qb,
            $this->getEntityName(),
            $property,
            $value,
            $this->getEntityName()
        ));
    }

    /**
     *
     * @param  array        $criteria
     * @param  QueryBuilder $qb
     * @param  string       $alias
     * @return QueryBuilder
     */
    protected function prepareComparisons(array &$criteria, QueryBuilder $qb, string $alias)
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            /*
             * Main QueryBuilder dispatch loop for
             * custom properties criteria.
             */
            $event = $this->dispatchQueryBuilderBuildEvent($qb, $key, $value);

            if (!$event->isPropagationStopped()) {
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $alias . '.', $key));
            }
        }

        return $qb;
    }

    /**
     * @param array  $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByCriteria(array &$criteria, QueryBuilder $qb): void
    {
        $simpleQB = new SimpleQueryBuilder($qb);
        foreach ($criteria as $key => $value) {
            $event = $this->dispatchQueryBuilderApplyEvent($qb, $key, $value);
            if (!$event->isPropagationStopped()) {
                $simpleQB->bindValue($key, $value);
            }
        }
    }

    /**
     * @param QueryBuilder $qb
     * @param string $name
     * @param string $key
     * @param mixed $value
     *
     * @return Query\Expr\Func
     */
    protected function directExprIn(QueryBuilder $qb, string $name, string $key, mixed $value): Query\Expr\Func
    {
        $newValue = [];

        if (is_array($value)) {
            foreach ($value as $singleValue) {
                if ($singleValue instanceof PersistableInterface) {
                    $newValue[] = $singleValue->getId();
                } else {
                    $newValue[] = $value;
                }
            }
        }

        return $qb->expr()->in($name, $newValue);
    }

    /**
     * Count entities using a Criteria object or a simple filter array.
     *
     * @param Criteria|mixed|array $criteria or array
     *
     * @return int
     */
    public function countBy(mixed $criteria): int
    {
        if ($criteria instanceof Criteria) {
            $collection = $this->matching($criteria);
            return $collection->count();
        } elseif (is_array($criteria)) {
            $qb = $this->createQueryBuilder(static::DEFAULT_ALIAS);
            $qb->select($qb->expr()->countDistinct(static::DEFAULT_ALIAS . '.id'));
            $qb = $this->prepareComparisons($criteria, $qb, static::DEFAULT_ALIAS);
            $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
            $this->applyFilterByCriteria($criteria, $qb);

            try {
                return (int) $qb->getQuery()->getSingleScalarResult();
            } catch (NoResultException | NonUniqueResultException $e) {
                return 0;
            }
        }
        return 0;
    }

    /**
     * @param ClassMetadataInfo $metadata
     * @return array
     */
    public static function getSearchableColumnsNames(ClassMetadataInfo $metadata): array
    {
        /*
         * Get fields needed for a search query
         */
        $criteriaFields = [];
        $cols = $metadata->getColumnNames();
        foreach ($cols as $col) {
            $field = $metadata->getFieldName($col);
            $type = $metadata->getTypeOfField($field);
            if (
                in_array($type, ['string', 'text']) &&
                !in_array($field, [
                    'color',
                    'folder',
                    'childrenOrder',
                    'childrenOrderDirection',
                    'password',
                    'token',
                    'confirmationToken'
                ])
            ) {
                $criteriaFields[] = $field;
            }
        }

        return $criteriaFields;
    }

    /**
     * Create a LIKE comparison with entity texts colunms.
     *
     * @param string $pattern
     * @param QueryBuilder $qb
     * @param string $alias
     * @return QueryBuilder
     */
    protected function classicLikeComparison(
        string $pattern,
        QueryBuilder $qb,
        string $alias = EntityRepository::DEFAULT_ALIAS
    ): QueryBuilder {
        $criteriaFields = [];
        foreach (static::getSearchableColumnsNames($this->getClassMetadata()) as $field) {
            $criteriaFields[$field] = '%' . strip_tags(mb_strtolower($pattern)) . '%';
        }

        foreach ($criteriaFields as $key => $value) {
            $fullKey = sprintf('LOWER(%s)', $alias . '.' . $key);
            $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
        }

        return $qb;
    }

    /**
     * Create a Criteria object from a search pattern and additional fields.
     *
     * @param string $pattern Search pattern
     * @param QueryBuilder $qb QueryBuilder to pass
     * @param array $criteria Additional criteria
     * @param string $alias SQL query table alias
     * @return QueryBuilder
     */
    protected function createSearchBy(
        string $pattern,
        QueryBuilder $qb,
        array &$criteria = [],
        string $alias = EntityRepository::DEFAULT_ALIAS
    ): QueryBuilder {
        $this->classicLikeComparison($pattern, $qb, $alias);
        $this->prepareComparisons($criteria, $qb, $alias);

        return $qb;
    }

    /**
     * @param string  $pattern  Search pattern
     * @param array   $criteria Additional criteria
     * @param array   $orders
     * @param int|null $limit
     * @param int|null $offset
     * @param string $alias
     *
     * @return array|Paginator
     * @psalm-return array<TEntityClass>|Paginator<TEntityClass>
     */
    public function searchBy(
        string $pattern,
        array $criteria = [],
        array $orders = [],
        ?int $limit = null,
        ?int $offset = null,
        string $alias = EntityRepository::DEFAULT_ALIAS
    ): array|Paginator {
        $qb = $this->createQueryBuilder($alias);
        $qb = $this->createSearchBy($pattern, $qb, $criteria, $alias);

        // Add ordering
        foreach ($orders as $key => $value) {
            if (
                (\str_starts_with($key, 'node.') || \str_starts_with($key, static::NODE_ALIAS . '.')) &&
                $this->hasJoinedNode($qb, $alias)
            ) {
                $key = preg_replace('#^node\.#', static::NODE_ALIAS . '.', $key);
                $qb->addOrderBy($key, $value);
            } elseif (
                \str_starts_with($key, static::NODESSOURCES_ALIAS . '.') &&
                $this->hasJoinedNodesSources($qb, $alias)
            ) {
                $qb->addOrderBy($key, $value);
            } else {
                $qb->addOrderBy($alias . '.' . $key, $value);
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
     * @param string $pattern Search pattern
     * @param array $criteria Additional criteria
     * @return int
     */
    public function countSearchBy(string $pattern, array $criteria = []): int
    {
        $qb = $this->createQueryBuilder(static::DEFAULT_ALIAS);
        $qb->select($qb->expr()->countDistinct(static::DEFAULT_ALIAS . '.id'));
        $qb = $this->createSearchBy($pattern, $qb, $criteria);

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByCriteria($criteria, $qb);

        try {
            return (int) $qb->getQuery()->getSingleScalarResult();
        } catch (NoResultException | NonUniqueResultException $e) {
            return 0;
        }
    }

    /**
     * @param array $criteria
     * @param QueryBuilder $qb
     * @param string $nodeAlias
     */
    protected function buildTagFiltering(array &$criteria, QueryBuilder $qb, string $nodeAlias = 'n'): void
    {
        if (key_exists('tags', $criteria)) {
            /*
             * Do not filter if tag is null
             */
            if (is_null($criteria['tags'])) {
                return;
            }

            if (is_array($criteria['tags']) || $criteria['tags'] instanceof Collection) {
                /*
                 * Do not filter if tag array is empty.
                 */
                if (count($criteria['tags']) === 0) {
                    return;
                }
                if (
                    in_array("tagExclusive", array_keys($criteria))
                    && $criteria["tagExclusive"] === true
                ) {
                    // To get an exclusive tag filter
                    // we need to filter against each tag id
                    // and to inner join with a different alias for each tag
                    // with AND operator
                    /**
                     * @var int $index
                     * @var Tag|null $tag Tag can be null if not found
                     */
                    foreach ($criteria['tags'] as $index => $tag) {
                        if ($tag instanceof Tag) {
                            $alias = 'ntg_' . $index;
                            $qb->innerJoin($nodeAlias . '.nodesTags', $alias);
                            $qb->andWhere($qb->expr()->eq($alias . '.tag', $tag->getId()));
                        }
                    }
                    unset($criteria["tagExclusive"]);
                    unset($criteria['tags']);
                } else {
                    $qb->innerJoin(
                        $nodeAlias . '.nodesTags',
                        'ntg_0',
                        'WITH',
                        'ntg_0.tag IN (:tags)'
                    );
                }
            } else {
                $qb->innerJoin(
                    $nodeAlias . '.nodesTags',
                    'ntg_0',
                    'WITH',
                    'ntg_0.tag = :tags'
                );
            }
        }
    }

    /**
     * Bind tag parameters to final query
     *
     * @param array $criteria
     * @param QueryBuilder $qb
     */
    protected function applyFilterByTag(array &$criteria, QueryBuilder $qb): void
    {
        if (key_exists('tags', $criteria)) {
            if ($criteria['tags'] instanceof Tag) {
                $qb->setParameter('tags', $criteria['tags']->getId());
            } elseif (is_array($criteria['tags']) || $criteria['tags'] instanceof Collection) {
                if (count($criteria['tags']) > 0) {
                    $qb->setParameter('tags', $criteria['tags']);
                }
            } elseif (is_integer($criteria['tags'])) {
                $qb->setParameter('tags', (int) $criteria['tags']);
            }
            unset($criteria['tags']);
        }
    }

    /**
     * Ensure that node table is joined only once.
     *
     * @param  QueryBuilder $qb
     * @param  string  $alias
     * @return bool
     */
    protected function hasJoinedNode(QueryBuilder $qb, string $alias)
    {
        return $this->joinExists($qb, $alias, static::NODE_ALIAS);
    }

    /**
     * Ensure that nodes_sources table is joined only once.
     *
     * @param  QueryBuilder $qb
     * @param  string  $alias
     * @return bool
     */
    protected function hasJoinedNodesSources(QueryBuilder $qb, string $alias)
    {
        return $this->joinExists($qb, $alias, static::NODESSOURCES_ALIAS);
    }

    /**
     * Ensure that nodes_sources table is joined only once.
     *
     * @param  QueryBuilder $qb
     * @param  string  $alias
     * @return bool
     */
    protected function hasJoinedNodeType(QueryBuilder $qb, string $alias)
    {
        return $this->joinExists($qb, $alias, static::NODETYPE_ALIAS);
    }

    /**
     * @param QueryBuilder $qb
     * @param string $rootAlias
     * @param string $joinAlias
     * @return bool
     */
    protected function joinExists(QueryBuilder $qb, string $rootAlias, string $joinAlias)
    {
        if (isset($qb->getDQLPart('join')[$rootAlias])) {
            foreach ($qb->getDQLPart('join')[$rootAlias] as $join) {
                if (
                    null !== $join &&
                    $join instanceof Join &&
                    $join->getAlias() === $joinAlias
                ) {
                    return true;
                }
            }
        }

        return false;
    }
}
