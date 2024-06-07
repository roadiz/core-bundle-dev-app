<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

final class NodeTypeTreeDto implements NodeTypeInterface
{
    public function __construct(
        private readonly string $name,
        private readonly bool $publishable,
        private readonly bool $reachable,
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

    public function getLabel(): string
    {
        return $this->getDisplayName();
    }

    public function isReachable(): bool
    {
        return $this->reachable;
    }

    public function getDescription(): ?string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getDescription method.');
    }

    public function isVisible(): bool
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement isVisible method.');
    }

    public function getSourceEntityClassName(): string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSourceEntityClassName method.');
    }

    public function getSourceEntityFullQualifiedClassName(): string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSourceEntityFullQualifiedClassName method.');
    }

    public function getSourceEntityTableName(): string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSourceEntityTableName method.');
    }

    public function getFieldsNames(): array
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getFieldsNames method.');
    }

    public function getFields(): Collection
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getFields method.');
    }

    public function getSearchableFields(): Collection
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSearchableFields method.');
    }

    public function getFieldByName(string $name): ?NodeTypeFieldInterface
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getFieldByName method.');
    }

    public function isSearchable(): bool
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement isSearchable method.');
    }
}
