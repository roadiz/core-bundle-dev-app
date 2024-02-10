<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Console;

use RZ\Roadiz\CoreBundle\SearchEngine\ClientRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SolrCommand extends Command
{
    protected ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ClientRegistry $clientRegistry,
        ?string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->setName('solr:check')
            ->setDescription('Check Solr search engine server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $client = $this->clientRegistry->getClient();
        $this->io = new SymfonyStyle($input, $output);

        if (null !== $client) {
            if (true === $this->clientRegistry->isClientReady($client)) {
                $this->io->writeln('<info>Solr search engine server is running…</info>');
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

    protected function displayBasicConfig(): void
    {
        if (null !== $this->io) {
            $this->io->error('No Solr search engine server has been configured…');
            $this->io->note(<<<EOD
Edit your config/packages/roadiz_core.yaml file to enable Solr (example):

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
}
