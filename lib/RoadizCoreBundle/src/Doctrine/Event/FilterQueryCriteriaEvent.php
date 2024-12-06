<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\Query;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterQueryCriteriaEvent extends Event
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(protected Query $query, protected string $entityClass, protected string $property, protected mixed $value)
    {
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * @return $this
     */
    public function setQuery(Query $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function getEntityClass(): string
    {
        return $this->entityClass;
    }

    public function getProperty(): string
    {
        return $this->property;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param class-string $entityClass
     */
    public function supports(string $entityClass): bool
    {
        return $this->entityClass === $entityClass;
    }
}
