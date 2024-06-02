<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

final class NodesSourcesTreeDto implements PersistableInterface
{
    public function __construct(
        private readonly ?int $id,
        private readonly ?string $title,
        private readonly ?\DateTime $publishedAt,
    ) {
    }

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
