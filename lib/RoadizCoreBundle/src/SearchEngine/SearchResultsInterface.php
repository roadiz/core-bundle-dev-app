<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

/**
 * @extends \Iterator<SolrSearchResultItemInterface>
 */
interface SearchResultsInterface extends \Iterator
{
    public function getResultCount(): int;

    /**
     * @return array<SolrSearchResultItemInterface>
     */
    public function getResultItems(): array;

    public function map(callable $callable): array;
}
