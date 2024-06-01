<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

/**
 * @extends \Iterator<SolrSearchResultItem>
 */
interface SearchResultsInterface extends \Iterator
{
    public function getResultCount(): int;
    /**
     * @return array<SolrSearchResultItem>
     */
    public function getResultItems(): array;
    public function map(callable $callable): array;
}
