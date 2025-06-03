<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use Solarium\Core\Client\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SolrCommand extends Command
{
    protected ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ClientRegistry $clientRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('solr:check')
            ->setDescription('Check Solr search engine server');
    }

    protected function validateSolrState(SymfonyStyle $io): ?Client
    {
        $client = $this->clientRegistry->getClient();

        if (null === $client) {
            $this->displayBasicConfig();

            return null;
        }

        if (true !== $this->clientRegistry->isClientReady($client)) {
            $io->error('Solr search engine server does not respond…');
            $io->note('See your `config/packages/roadiz_core.yaml` file to correct your Solr connection settings.');

            return null;
        }

        return $client;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        if (null === $this->validateSolrState($this->io)) {
            return 1;
        }

        $this->io->success('Solr search engine server is running.');

        return 0;
    }

    protected function displayBasicConfig(): void
    {
        if (null === $this->io) {
            return;
        }

        $this->io->error('No Solr search engine server has been configured…');
        $this->io->note(<<<EOD
Edit your `config/packages/roadiz_core.yaml` file to enable Solr (example):

solr:
    endpoint:
        localhost:
            host: "localhost"
            port: "8983"
            path: "/"
            core: "roadiz"
            timeout: 3
            username: ""
            password: ""
EOD);
    }
}
