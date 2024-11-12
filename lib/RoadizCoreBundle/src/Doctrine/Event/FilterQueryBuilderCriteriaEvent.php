<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterQueryBuilderCriteriaEvent extends Event
{
    /**
     * @param class-string $entityClass
     * @param class-string $actualEntityName
     */
    public function __construct(
        protected QueryBuilder $queryBuilder,
        protected string $entityClass,
        protected string $property,
        protected mixed $value,
        protected string $actualEntityName,
    ) {
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    public function supports(): bool
    {
        return $this->entityClass === $this->actualEntityName;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getActualEntityName(): string
    {
        return $this->actualEntityName;
    }
}
