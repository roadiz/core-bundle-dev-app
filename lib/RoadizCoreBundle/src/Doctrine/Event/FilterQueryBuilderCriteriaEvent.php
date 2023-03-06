<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\QueryBuilder;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\Event
 */
abstract class FilterQueryBuilderCriteriaEvent extends Event
{
    protected string $property;
    /**
     * @var mixed
     */
    protected $value;
    protected QueryBuilder $queryBuilder;
    /**
     * @var class-string
     */
    protected string $entityClass;
    /**
     * @var class-string
     */
    protected string $actualEntityName;

    /**
     * @param QueryBuilder $queryBuilder
     * @param class-string $entityClass
     * @param string $property
     * @param mixed $value
     * @param class-string $actualEntityName
     */
    public function __construct(QueryBuilder $queryBuilder, string $entityClass, string $property, $value, string $actualEntityName)
    {
        $this->queryBuilder = $queryBuilder;
        $this->entityClass = $entityClass;
        $this->property = $property;
        $this->value = $value;
        $this->actualEntityName = $actualEntityName;
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
     * @return FilterQueryBuilderCriteriaEvent
     */
    public function setQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $this->queryBuilder = $queryBuilder;
        return $this;
    }

    /**
     * @return string
     */
    public function getProperty()
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function supports(): bool
    {
        return $this->entityClass === $this->actualEntityName;
    }

    /**
     * @return string
     */
    public function getActualEntityName()
    {
        return $this->actualEntityName;
    }
}
