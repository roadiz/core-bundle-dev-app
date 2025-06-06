<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Nelmio\SolariumBundle\ClientRegistry;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\CliAwareIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SolrOptimizeCommand extends SolrCommand
{
    public function __construct(
        protected readonly IndexerFactoryInterface $indexerFactory,
        #[Autowire(service: 'solarium.client_registry')]
        ClientRegistry $clientRegistry,
        ?string $name = null,
    ) {
        parent::__construct($clientRegistry, $name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('solr:optimize')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'Solr client name to use', default: null)
            ->setDescription('Optimize Solr search engine index');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null === $this->validateSolrState($this->io, $input->getOption('client') ?: null)) {
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
