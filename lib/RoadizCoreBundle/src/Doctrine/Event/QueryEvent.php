<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\Event;

use Doctrine\ORM\Query;
use Symfony\Contracts\EventDispatcher\Event;

class QueryEvent extends Event
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(protected Query $query, protected string $entityClass)
    {
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    /**
     * @return class-string
     */
    public function getEntityClass(): string
    {
        return $this->entityClass;
    }
}
