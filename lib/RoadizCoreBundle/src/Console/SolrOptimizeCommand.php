<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\CliAwareIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SolrOptimizeCommand extends SolrCommand
{
    public function __construct(
        protected readonly IndexerFactoryInterface $indexerFactory,
        ClientRegistry $clientRegistry,
        ?string $name = null,
    ) {
        parent::__construct($clientRegistry, $name);
    }

    protected function configure(): void
    {
        $this->setName('solr:optimize')
            ->setDescription('Optimize Solr search engine index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null === $this->validateSolrState($this->io)) {
            return 1;
        }

        $documentIndexer = $this->indexerFactory->getIndexerFor(Document::class);
        if ($documentIndexer instanceof CliAwareIndexer) {
            $documentIndexer->setIo($this->io);
        }
        $documentIndexer->optimizeSolr();
        $this->io->success('Solr core has been optimized.');

        return 0;
    }
}
