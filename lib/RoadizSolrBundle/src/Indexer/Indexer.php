<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Indexer;

interface Indexer
{
    public function reindexAll(): void;

    public function index(mixed $id): void;

    public function delete(mixed $id): void;

    public function emptySolr(?string $documentType = null): void;

    public function optimizeSolr(): void;
}
