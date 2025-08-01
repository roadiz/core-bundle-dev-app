<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Event;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\Event;

final class SolrInitializationEvent extends Event
{
    public function __construct(
        public readonly string $baseUrl,
        public readonly string $solrCollectionName,
        public readonly SymfonyStyle $io,
    ) {
    }
}
