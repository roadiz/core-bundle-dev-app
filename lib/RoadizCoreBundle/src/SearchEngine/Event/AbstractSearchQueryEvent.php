<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Event;

use Solarium\QueryType\Select\Query\Query;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractSearchQueryEvent extends Event
{
    private Query $query;
    private array $args;

    public function __construct(Query $query, array $args)
    {
        $this->query = $query;
        $this->args = $args;
    }

    public function getQuery(): Query
    {
        return $this->query;
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}
