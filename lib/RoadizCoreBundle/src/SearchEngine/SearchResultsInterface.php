<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

/**
 * @extends \Iterator<SearchResultItemInterface>
 */
interface SearchResultsInterface extends \Iterator
{
    public function getResultCount(): int;

    /**
     * @return array<SearchResultItemInterface>
     */
    public function getResultItems(): array;

    public function map(callable $callable): array;
}
