<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterNodeEvent extends Event
{
    protected Node $node;

    public function __construct(Node $node)
    {
        $this->node = $node;
    }

    public function getNode(): Node
    {
        return $this->node;
    }
}
