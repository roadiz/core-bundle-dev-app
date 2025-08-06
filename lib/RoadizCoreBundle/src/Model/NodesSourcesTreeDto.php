<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

final readonly class NodesSourcesTreeDto implements PersistableInterface
{
    public function __construct(
        private ?int $id,
        private ?string $title,
        private ?\DateTime $publishedAt,
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

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }
}
