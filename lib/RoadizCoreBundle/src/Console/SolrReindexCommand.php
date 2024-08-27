<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\BatchIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\CliAwareIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumDocumentTranslation;
use RZ\Roadiz\CoreBundle\SearchEngine\SolariumNodeSource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class SolrReindexCommand extends SolrCommand implements ThemeAwareCommandInterface
{
    public function __construct(
        protected readonly IndexerFactoryInterface $indexerFactory,
        ClientRegistry $clientRegistry,
        ?string $name = null
    ) {
        parent::__construct($clientRegistry, $name);
    }

    protected function configure(): void
    {
        $this->setName('solr:reindex')
            ->setDescription('Reindex Solr search engine index')
            ->addOption('nodes', null, InputOption::VALUE_NONE, 'Reindex with only nodes.')
            ->addOption('documents', null, InputOption::VALUE_NONE, 'Reindex with only documents.')
            ->addOption('batch-count', null, InputOption::VALUE_REQUIRED, 'Split reindexing in batch (only for nodes).')
            ->addOption('batch-number', null, InputOption::VALUE_REQUIRED, 'Run a selected batch (only for nodes), <comment>first batch is 0</comment>.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null === $this->validateSolrState($this->io)) {
            return 1;
        }

        if (
            $this->io->confirm(
                'Are you sure to reindex your Node and Document database?',
                !$input->isInteractive()
            )
        ) {
            $stopwatch = new Stopwatch();
            $stopwatch->start('global');

            if ($input->getOption('documents')) {
                $this->executeForDocuments($stopwatch);
            } elseif ($input->getOption('nodes')) {
                $batchCount = (int) ($input->getOption('batch-count') ?? 1);
                $batchNumber = (int) ($input->getOption('batch-number') ?? 0);
                $this->executeForNodes($stopwatch, $batchCount, $batchNumber);
            } else {
                $this->executeForAll($stopwatch);
            }
        }
        return 0;
    }

    protected function executeForAll(Stopwatch $stopwatch): void
    {
        // Empty first
        $documentIndexer = $this->indexerFactory->getIndexerFor(Document::class);
        if ($documentIndexer instanceof CliAwareIndexer) {
            $documentIndexer->setIo($this->io);
        }
        $nodesSourcesIndexer = $this->indexerFactory->getIndexerFor(NodesSources::class);
        if ($nodesSourcesIndexer instanceof CliAwareIndexer) {
            $nodesSourcesIndexer->setIo($this->io);
        }
        $nodesSourcesIndexer->emptySolr();
        $documentIndexer->reindexAll();
        $nodesSourcesIndexer->reindexAll();

        $stopwatch->stop('global');
        $duration = $stopwatch->getEvent('global')->getDuration();
        $this->io->success(sprintf('Node and document database has been re-indexed in %.2d ms.', $duration));
    }

    protected function executeForDocuments(Stopwatch $stopwatch): void
    {
        $documentIndexer = $this->indexerFactory->getIndexerFor(Document::class);
        if ($documentIndexer instanceof CliAwareIndexer) {
            $documentIndexer->setIo($this->io);
        }
        $documentIndexer->emptySolr(SolariumDocumentTranslation::DOCUMENT_TYPE);
        $documentIndexer->reindexAll();

        $stopwatch->stop('global');
        $duration = $stopwatch->getEvent('global')->getDuration();
        $this->io->success(sprintf('Document database has been re-indexed in %.2d ms.', $duration));
    }

    protected function executeForNodes(Stopwatch $stopwatch, int $batchCount, int $batchNumber): void
    {
        $nodesSourcesIndexer = $this->indexerFactory->getIndexerFor(NodesSources::class);
        if ($nodesSourcesIndexer instanceof CliAwareIndexer) {
            $nodesSourcesIndexer->setIo($this->io);
        }
        // Empty first ONLY if one batch or first batch.
        if ($batchNumber === 0) {
            $nodesSourcesIndexer->emptySolr(SolariumNodeSource::DOCUMENT_TYPE);
        }

        if ($nodesSourcesIndexer instanceof BatchIndexer) {
            $nodesSourcesIndexer->reindexAll($batchCount, $batchNumber);
        } else {
            $nodesSourcesIndexer->reindexAll();
        }

        $stopwatch->stop('global');
        $duration = $stopwatch->getEvent('global')->getDuration();
        if ($batchCount > 1) {
            $this->io->success(sprintf(
                'Batch %d/%d of node database has been re-indexed in %.2d ms.',
                $batchNumber + 1,
                $batchCount,
                $duration
            ));
        } else {
            $this->io->success(sprintf('Node database has been re-indexed in %.2d ms.', $duration));
        }
    }
}
