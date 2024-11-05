<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

interface ExplorerItemInterface
{
    public function getId(): string|int;

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
