<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

/**
 * Doctrine Data transfer object to represent a Tag in a tree.
 */
final readonly class TagTreeDto implements PersistableInterface
{
    public function __construct(
        private int $id,
        private string $tagName,
        private ?string $name,
        private string $color,
        private bool $visible,
        private ?int $parentId,
    ) {
    }

    #[\Override]
    public function getId(): int
    {
        return $this->id;
    }

    public function getTagName(): string
    {
        return $this->tagName;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }
}
