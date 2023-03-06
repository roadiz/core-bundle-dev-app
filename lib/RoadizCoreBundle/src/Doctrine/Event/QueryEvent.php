<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\Query;
use Symfony\Contracts\EventDispatcher\Event;

class QueryEvent extends Event
{
    protected Query $query;

    /**
     * @var class-string
     */
    protected string $entityClass;

    /**
     * @param Query  $query
     * @param string $entityClass
     */
    public function __construct(Query $query, string $entityClass)
    {
        $this->query = $query;
        $this->entityClass = $entityClass;
    }

    /**
     * @return Query
     */
    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
