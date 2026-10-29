<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

interface FacetedSearchResultsInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getFacets(): array;
}
