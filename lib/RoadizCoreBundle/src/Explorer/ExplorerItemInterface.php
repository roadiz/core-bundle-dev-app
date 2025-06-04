<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

use Symfony\Component\Uid\Uuid;

interface ExplorerItemInterface
{
    public function getId(): string|int|Uuid;

    public function getAlternativeDisplayable(): ?string;

    public function getDisplayable(): string;

    /**
     * Get original item.
     */
    public function getOriginal(): mixed;

    /**
     * Return a structured array of data.
     */
    public function toArray(): array;
}
