<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

interface ExplorerItemInterface
{
    /**
     * @return string|int
     */
    public function getId(): string|int;

    /**
     * @return string|null
     */
    public function getAlternativeDisplayable(): ?string;

    /**
     * @return string
     */
    public function getDisplayable(): string;

    /**
     * Get original item.
     *
     * @return mixed
     */
    public function getOriginal(): mixed;

    /**
     * Return a structured array of data.
     *
     * @return array
     */
    public function toArray(): array;
}
