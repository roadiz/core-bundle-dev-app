<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\Event
 */
abstract class FilterQueryBuilderEvent extends Event
{
    /**
     * @var QueryBuilder
     */
    private QueryBuilder $queryBuilder;
    /**
     * @var class-string
     */
    private string $entityClass;

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $entityClass
     */
    public function __construct(QueryBuilder $queryBuilder, string $entityClass)
    {
        $this->queryBuilder = $queryBuilder;
        $this->entityClass = $entityClass;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @return FilterQueryBuilderEvent
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }


    /**
     * @param string $entityClass
     * @return bool
     */
    public function supports($entityClass): bool
    {
        return $this->entityClass === $entityClass;
    }
}
