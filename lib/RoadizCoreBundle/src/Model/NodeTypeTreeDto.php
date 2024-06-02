<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

final class NodeTypeTreeDto
{
    public function __construct(
        private readonly string $name,
        private readonly bool $publishable,
        private readonly string $displayName,
        private readonly string $color,
        private readonly bool $hidingNodes,
        private readonly bool $hidingNonReachableNodes,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function isHidingNodes(): bool
    {
        return $this->hidingNodes;
    }

    public function isHidingNonReachableNodes(): bool
    {
        return $this->hidingNonReachableNodes;
    }
}
