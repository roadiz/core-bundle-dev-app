<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use Nelmio\SolariumBundle\ClientRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\CliAwareIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SolrResetCommand extends SolrCommand
{
    public function __construct(
        private readonly IndexerFactoryInterface $indexerFactory,
        #[Autowire(service: 'solarium.client_registry')]
        ClientRegistry $clientRegistry,
        ?string $name = null,
    ) {
        parent::__construct($clientRegistry, $name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('solr:reset')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'Solr client name to use', default: null)
            ->setDescription('Reset Solr search engine index');
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null === $this->validateSolrState($this->io, $input->getOption('client') ?: null)) {
            return 1;
        }
        $confirmation = new ConfirmationQuestion(
            '<question>Are you sure to reset Solr index?</question>',
            false
        );
        if ($this->io->askQuestion($confirmation)) {
            $indexer = $this->indexerFactory->getIndexerFor(NodesSources::class);
            if ($indexer instanceof CliAwareIndexer) {
                $indexer->setIo($this->io);
            }
            $indexer->emptySolr();
            $this->io->success('Solr index resetted.');
        }

        return 0;
    }
}
