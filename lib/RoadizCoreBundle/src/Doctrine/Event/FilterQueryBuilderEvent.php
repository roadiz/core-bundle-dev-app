<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterQueryBuilderEvent extends Event
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(private QueryBuilder $queryBuilder, private readonly string $entityClass)
    {
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return FilterQueryBuilderEvent
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    /**
     * @param class-string $entityClass
     */
    public function supports(string $entityClass): bool
    {
        return $this->entityClass === $entityClass;
    }
}
