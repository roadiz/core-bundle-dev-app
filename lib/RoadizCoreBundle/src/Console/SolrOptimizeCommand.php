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

/**
 * Command line utils for managing nodes from terminal.
 */
class SolrOptimizeCommand extends SolrCommand
{
    protected IndexerFactoryInterface $indexerFactory;

    /**
     * @param ClientRegistry $clientRegistry
     * @param IndexerFactoryInterface $indexerFactory
     */
    public function __construct(ClientRegistry $clientRegistry, IndexerFactoryInterface $indexerFactory)
    {
        parent::__construct($clientRegistry);
        $this->indexerFactory = $indexerFactory;
    }

    protected function configure(): void
    {
        $this->setName('solr:optimize')
            ->setDescription('Optimize Solr search engine index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $solr = $this->clientRegistry->getClient();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $solr) {
            if (true === $this->clientRegistry->isClientReady($solr)) {
                $documentIndexer = $this->indexerFactory->getIndexerFor(Document::class);
                if ($documentIndexer instanceof CliAwareIndexer) {
                    $documentIndexer->setIo($this->io);
                }
                $documentIndexer->optimizeSolr();
                $this->io->success('<info>Solr core has been optimized.</info>');
            } else {
                $this->io->error('Solr search engine server does not respond…');
                $this->io->note('See your config.yml file to correct your Solr connexion settings.');
                return 1;
            }
        } else {
            $this->displayBasicConfig();
        }
        return 0;
    }
}
