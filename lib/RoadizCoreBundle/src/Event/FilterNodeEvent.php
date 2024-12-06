<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterNodeEvent extends Event
{
    public function __construct(protected readonly Node $node)
    {
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
