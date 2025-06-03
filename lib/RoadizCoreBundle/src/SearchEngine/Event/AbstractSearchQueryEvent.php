<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Event;

use Solarium\QueryType\Select\Query\Query;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractSearchQueryEvent extends Event
{
    public function __construct(private readonly Query $query, private readonly array $args)
    {
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
