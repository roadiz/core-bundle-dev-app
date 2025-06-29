<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\StatusAwareEntityInterface;

final readonly class NodesSourcesTreeDto implements PersistableInterface, StatusAwareEntityInterface
{
    public function __construct(
        private ?int $id,
        private ?string $title,
        private ?\DateTime $publishedAt,
        private ?\DateTime $deletedAt,
    ) {
    }

    #[\Override]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    #[\Override]
    public function isPublished(): bool
    {
        return null !== $this->publishedAt && $this->publishedAt <= new \DateTime();
    }

    #[\Override]
    public function isDraft(): bool
    {
        return !$this->isPublished() && !$this->isDeleted();
    }

    #[\Override]
    public function isDeleted(): bool
    {
        return null !== $this->deletedAt && $this->deletedAt <= new \DateTime();
    }

    #[\Override]
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    #[\Override]
    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    #[\Override]
    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        return $this;
    }

    #[\Override]
    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        return $this;
    }
}
