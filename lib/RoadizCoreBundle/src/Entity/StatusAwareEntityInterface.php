<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

interface StatusAwareEntityInterface
{
    public function isPublished(): bool;

    public function isDraft(): bool;

    public function isDeleted(): bool;

    public function getPublishedAt(): ?\DateTime;

    public function getDeletedAt(): ?\DateTime;

    public function setPublishedAt(?\DateTime $publishedAt): self;

    public function setDeletedAt(?\DateTime $deletedAt): self;
}
