<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\CliAwareIndexer;
use RZ\Roadiz\CoreBundle\SearchEngine\Indexer\IndexerFactoryInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

final class SolrResetCommand extends SolrCommand
{
    public function __construct(
        private readonly IndexerFactoryInterface $indexerFactory,
        ClientRegistry $clientRegistry,
        ?string $name = null
    ) {
        parent::__construct($clientRegistry, $name);
    }

    protected function configure(): void
    {
        $this->setName('solr:reset')
            ->setDescription('Reset Solr search engine index');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $solr = $this->clientRegistry->getClient();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $solr) {
            if (true === $this->clientRegistry->isClientReady($solr)) {
                $confirmation = new ConfirmationQuestion(
                    '<question>Are you sure to reset Solr index?</question>',
                    false
                );
                if ($this->io->askQuestion($confirmation)) {
                    $indexer  = $this->indexerFactory->getIndexerFor(NodesSources::class);
                    if ($indexer instanceof CliAwareIndexer) {
                        $indexer->setIo($this->io);
                    }
                    $indexer->emptySolr();
                    $this->io->success('Solr index resetted.');
                }
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
