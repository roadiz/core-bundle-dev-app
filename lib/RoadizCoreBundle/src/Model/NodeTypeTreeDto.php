<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

final readonly class NodeTypeTreeDto implements NodeTypeInterface
{
    public function __construct(
        private string $name,
        private bool $publishable,
        private bool $reachable,
        private string $displayName,
        private string $color,
        private bool $hidingNodes,
        private bool $hidingNonReachableNodes,
    ) {
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name;
    }

    #[\Override]
    public function isPublishable(): bool
    {
        return $this->publishable;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    #[\Override]
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

    #[\Override]
    public function getLabel(): string
    {
        return $this->getDisplayName();
    }

    #[\Override]
    public function isReachable(): bool
    {
        return $this->reachable;
    }

    #[\Override]
    public function getDescription(): ?string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getDescription method.');
    }

    #[\Override]
    public function isVisible(): bool
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement isVisible method.');
    }

    #[\Override]
    public function getSourceEntityClassName(): string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSourceEntityClassName method.');
    }

    #[\Override]
    public function getSourceEntityFullQualifiedClassName(): string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSourceEntityFullQualifiedClassName method.');
    }

    #[\Override]
    public function getSourceEntityTableName(): string
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSourceEntityTableName method.');
    }

    #[\Override]
    public function getFieldsNames(): array
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getFieldsNames method.');
    }

    #[\Override]
    public function getFields(): Collection
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getFields method.');
    }

    #[\Override]
    public function getSearchableFields(): Collection
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getSearchableFields method.');
    }

    #[\Override]
    public function getFieldByName(string $name): ?NodeTypeFieldInterface
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement getFieldByName method.');
    }

    #[\Override]
    public function isSearchable(): bool
    {
        throw new \RuntimeException('NodeTypeTreeDto does not implement isSearchable method.');
    }
}
