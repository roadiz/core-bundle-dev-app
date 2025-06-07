<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

/**
 * @template T of object
 */
interface SearchResultItemInterface
{
    /**
     * @return T
     */
    public function getItem(): object;

    /**
     * @return array<string, array<string>>
     */
    public function getHighlighting(): array;
}
