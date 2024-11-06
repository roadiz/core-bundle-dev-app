<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Node;

abstract class FilterNodePathEvent extends FilterNodeEvent
{
    protected array $paths;
    protected ?\DateTime $updatedAt;

    public function __construct(Node $node, array $paths = [], ?\DateTime $updatedAt = null)
    {
        parent::__construct($node);
        $this->paths = $paths;
        $this->updatedAt = $updatedAt;
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
