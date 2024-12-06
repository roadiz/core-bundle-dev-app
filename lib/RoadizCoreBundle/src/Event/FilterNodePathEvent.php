<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;

abstract class FilterNodePathEvent extends FilterNodeEvent
{
    public function __construct(
        Node $node,
        protected readonly array $paths = [],
        protected readonly ?\DateTime $updatedAt = null,
    ) {
        parent::__construct($node);
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}
