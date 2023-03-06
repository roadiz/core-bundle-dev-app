<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

interface BatchIndexer extends Indexer
{
    public function reindexAll(int $batchCount = 1, int $batchNumber = 0): void;
}
