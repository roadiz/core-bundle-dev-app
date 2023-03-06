<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine\Indexer;

use Symfony\Component\Console\Style\SymfonyStyle;

interface CliAwareIndexer extends Indexer
{
    public function setIo(?SymfonyStyle $io): self;
}
