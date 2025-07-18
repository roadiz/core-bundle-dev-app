<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Indexer;

interface IndexerFactoryInterface
{
    /**
     * @param class-string $classname
     */
    public function getIndexerFor(string $classname): Indexer;
}
