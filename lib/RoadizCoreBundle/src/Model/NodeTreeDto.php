<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;

/**
 * Doctrine Data transfer object to represent a Node in a tree.
 */
final class NodeTreeDto implements NodeInterface
{
    public NodeTypeTreeDto $nodeType;
    public NodesSourcesTreeDto $nodeSource;

    public function __construct(
        private readonly int $id,
        private readonly string $nodeName,
        private readonly bool $hideChildren,
        private readonly bool $home,
        private readonly bool $visible,
        private readonly NodeStatus $status,
        private readonly ?int $parentId,
        private readonly string $childrenOrder,
        private readonly string $childrenOrderDirection,
        private readonly bool $locked,
        // NodeType
        string $nodeTypeName,
        bool $nodeTypePublishable,
        bool $nodeTypeReachable,
        string $nodeTypeDisplayName,
        string $nodeTypeColor,
        bool $nodeTypeHidingNodes,
        bool $nodeTypeHidingNonReachableNodes,
        // Node source
        ?int $sourceId,
        ?string $title,
        ?\DateTime $publishedAt,
    ) {
        $this->nodeType = new NodeTypeTreeDto(
            $nodeTypeName,
            $nodeTypePublishable,
            $nodeTypeReachable,
            $nodeTypeDisplayName,
            $nodeTypeColor,
            $nodeTypeHidingNodes,
            $nodeTypeHidingNonReachableNodes
        );
        $this->nodeSource = new NodesSourcesTreeDto(
            $sourceId,
            $title,
            $publishedAt,
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    public function getChildrenOrderDirection(): string
    {
        return $this->childrenOrderDirection;
    }

    public function getNodeName(): string
    {
        return $this->nodeName;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function isHidingChildren(): bool
    {
        return $this->hideChildren;
    }

    public function isHome(): bool
    {
        return $this->home;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function getStatus(): NodeStatus
    {
        return $this->status;
    }

    public function getStatusAsString(): string
    {
        return $this->status->name;
    }

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function isPublished(): bool
    {
        return $this->status->isPublished();
    }

    public function isPending(): bool
    {
        return $this->status->isPending();
    }

    public function isDraft(): bool
    {
        return $this->status->isDraft();
    }

    public function isDeleted(): bool
    {
        return $this->status->isDeleted();
    }

    public function getNodeType(): NodeTypeInterface
    {
        return $this->nodeType;
    }

    public function getNodeSource(): NodesSourcesTreeDto
    {
        return $this->nodeSource;
    }
}
