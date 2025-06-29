<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation\Versioned;
use RZ\Roadiz\CoreBundle\Api\Filter\ArchiveFilter;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;

trait StatusAwareEntityTrait
{
    #[ApiFilter(BaseFilter\DateFilter::class)]
    #[ApiFilter(BaseFilter\OrderFilter::class)]
    #[ApiFilter(ArchiveFilter::class)]
    #[Column(name: 'published_at', type: 'datetime', unique: false, nullable: true)]
    #[Groups(['nodes_sources', 'nodes_sources_base'])]
    #[Versioned]
    #[ApiProperty(
        description: 'Content publication date and time',
    )]
    protected ?\DateTime $publishedAt = null;

    #[ApiFilter(BaseFilter\DateFilter::class)]
    #[ApiFilter(BaseFilter\OrderFilter::class)]
    #[ApiFilter(ArchiveFilter::class)]
    #[Column(name: 'deleted_at', type: 'datetime', unique: false, nullable: true)]
    #[Ignore]
    #[Versioned]
    protected ?\DateTime $deletedAt = null;

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

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt && $this->deletedAt <= new \DateTime();
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @internal use workflow with StatusAwareEntityMarkingStore instead
     *
     * @return $this
     */
    public function setPublishedAt(?\DateTime $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @internal use workflow with StatusAwareEntityMarkingStore instead
     *
     * @return $this
     */
    public function setDeletedAt(?\DateTime $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}
