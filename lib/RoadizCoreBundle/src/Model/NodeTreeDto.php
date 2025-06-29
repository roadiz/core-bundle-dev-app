<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;

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
        private readonly ?int $parentId,
        private readonly string $childrenOrder,
        private readonly string $childrenOrderDirection,
        private readonly bool $locked,
        // NodeType
        private readonly string $nodeTypeName,
        // Node source
        ?int $sourceId,
        ?string $title,
        ?\DateTime $publishedAt,
        ?\DateTime $deletedAt,
    ) {
        $this->nodeSource = new NodesSourcesTreeDto(
            $sourceId,
            $title,
            $publishedAt,
            $deletedAt,
        );
    }

    #[\Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[\Override]
    public function getChildrenOrder(): string
    {
        return $this->childrenOrder;
    }

    #[\Override]
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

    public function isLocked(): bool
    {
        return $this->locked;
    }

    public function isPublished(): bool
    {
        return $this->nodeSource->isPublished();
    }

    public function isDraft(): bool
    {
        return $this->nodeSource->isDraft();
    }

    public function isDeleted(): bool
    {
        return $this->nodeSource->isDeleted();
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->nodeSource->getPublishedAt();
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->nodeSource->getDeletedAt();
    }

    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        throw new \LogicException('Node status dates must be set on their nodes-sources.');
    }

    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        throw new \LogicException('Node status dates must be set on their nodes-sources.');
    }

    public function getNodeSource(): NodesSourcesTreeDto
    {
        return $this->nodeSource;
    }

    #[\Override]
    public function getNodeTypeName(): string
    {
        return $this->nodeTypeName;
    }
}
