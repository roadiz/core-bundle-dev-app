<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;

/**
 * Class PrefixAwareRepository for defining join-queries prefixes.
 *
 * @template TEntityClass of object
 *
 * @extends EntityRepository<TEntityClass>
 */
abstract class PrefixAwareRepository extends EntityRepository
{
    /**
     * @var array
     *
     * array [
     *    'nodeType' => [
     *       'type': 'inner',
     *       'joins': [
     *           'n': 'obj.node',
     *           't': 'node.nodeType'
     *        ]
     *    ]
     * ]
     */
    private array $prefixes = [];

    public function getPrefixes(): array
    {
        return $this->prefixes;
    }

    public function getDefaultPrefix(): string
    {
        return EntityRepository::DEFAULT_ALIAS;
    }

    /**
     * @param string $prefix Ex. 'node'
     * @param array  $joins  Ex. ['n': 'obj.node']
     * @param string $type   Ex. 'inner'|'left', default 'left'
     *
     * @return $this
     */
    public function addPrefix(string $prefix, array $joins, string $type = 'left'): self
    {
        if (!in_array($type, ['left', 'inner'])) {
            throw new \InvalidArgumentException('Prefix type can only be "left" or "inner"');
        }

        if (!array_key_exists($prefix, $this->prefixes)) {
            $this->prefixes[$prefix] = [
                'joins' => $joins,
                'type' => $type,
            ];
        }

        return $this;
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
                $realKey = $this->getRealKey($qb, $key);
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($value, $realKey['prefix'], $realKey['key'], $baseKey));
            }
        }

        return $qb;
    }

    protected function getRealKey(QueryBuilder $qb, string $key): array
    {
        $keyParts = explode('.', $key);
        if (count($keyParts) > 1) {
            if (array_key_exists($keyParts[0], $this->prefixes)) {
                $lastPrefix = '';
                foreach ($this->prefixes[$keyParts[0]]['joins'] as $prefix => $field) {
                    if (!$this->hasJoinedPrefix($qb, $prefix)) {
                        switch ($this->prefixes[$keyParts[0]]['type']) {
                            case 'inner':
                                $qb->innerJoin($field, $prefix);
                                break;
                            case 'left':
                                $qb->leftJoin($field, $prefix);
                                break;
                        }
                    }

                    $lastPrefix = $prefix;
                }

                return [
                    'prefix' => $lastPrefix.'.',
                    'key' => $keyParts[1],
                ];
            }

            throw new \InvalidArgumentException('"'.$keyParts[0].'" prefix is not known for initiating joined queries.');
        }

        return [
            'prefix' => $this->getDefaultPrefix().'.',
            'key' => $key,
        ];
    }

    protected function hasJoinedPrefix(QueryBuilder $qb, string $prefix): bool
    {
        return $this->joinExists($qb, $this->getDefaultPrefix(), $prefix);
    }

    /**
     * Count entities using a Criteria object or a simple filter array.
     *
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array<TEntityClass>
     *
     * @throws \Exception
     */
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null,
    ): array {
        $qb = $this->createQueryBuilder($this->getDefaultPrefix());
        $qb->select($this->getDefaultPrefix());
        $qb = $this->prepareComparisons($criteria, $qb, $this->getDefaultPrefix());

        // Add ordering
        if (null !== $orderBy) {
            foreach ($orderBy as $key => $value) {
                $realKey = $this->getRealKey($qb, $key);
                $qb->addOrderBy($realKey['prefix'].$realKey['key'], $value);
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
     * Count entities using a Criteria object or a simple filter array.
     *
     * @psalm-return TEntityClass
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneBy(
        array $criteria,
        ?array $orderBy = null,
    ): ?object {
        $qb = $this->createQueryBuilder($this->getDefaultPrefix());
        $qb->select($this->getDefaultPrefix());
        $qb = $this->prepareComparisons($criteria, $qb, $this->getDefaultPrefix());

        // Add ordering
        if (null !== $orderBy) {
            foreach ($orderBy as $key => $value) {
                $realKey = $this->getRealKey($qb, $key);
                $qb->addOrderBy($realKey['prefix'].$realKey['key'], $value);
            }
        }

        $qb->setMaxResults(1);
        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByCriteria($criteria, $qb);
        $query = $qb->getQuery();
        $this->dispatchQueryEvent($query);

        return $query->getOneOrNullResult();
    }

    /**
     * @param string $pattern  Search pattern
     * @param array  $criteria Additional criteria
     * @param int    $limit
     * @param int    $offset
     *
     * @return array<TEntityClass>
     */
    public function searchBy(
        string $pattern,
        array $criteria = [],
        array $orders = [],
        $limit = null,
        $offset = null,
        string $alias = EntityRepository::DEFAULT_ALIAS,
    ): array {
        $qb = $this->createQueryBuilder($alias);
        $qb->select($alias);
        $qb = $this->createSearchBy($pattern, $qb, $criteria, $alias);

        // Add ordering
        if (null !== $orders) {
            foreach ($orders as $key => $value) {
                $realKey = $this->getRealKey($qb, $key);
                $qb->addOrderBy($realKey['prefix'].$realKey['key'], $value);
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

    public function countSearchBy(string $pattern, array $criteria = []): int
    {
        $qb = $this->createQueryBuilder($this->getDefaultPrefix());
        $qb->select($qb->expr()->countDistinct($this->getDefaultPrefix().'.id'));
        $qb = $this->createSearchBy($pattern, $qb, $criteria);

        $this->dispatchQueryBuilderEvent($qb, $this->getEntityName());
        $this->applyFilterByCriteria($criteria, $qb);

        return (int) $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Create a LIKE comparison with entity texts columns.
     */
    protected function classicLikeComparison(
        string $pattern,
        QueryBuilder $qb,
        string $alias = 'obj',
    ): QueryBuilder {
        /*
         * Get fields needed for a search query
         */
        $criteriaFields = [];
        foreach (static::getSearchableColumnsNames($this->getClassMetadata()) as $field) {
            $criteriaFields[$field] = '%'.strip_tags(\mb_strtolower($pattern)).'%';
        }

        foreach ($criteriaFields as $key => $value) {
            if (\is_string($key)) {
                $realKey = $this->getRealKey($qb, $key);
                $fullKey = sprintf('LOWER(%s)', $realKey['prefix'].$realKey['key']);
                $qb->orWhere($qb->expr()->like($fullKey, $qb->expr()->literal($value)));
            }
        }

        return $qb;
    }
}
