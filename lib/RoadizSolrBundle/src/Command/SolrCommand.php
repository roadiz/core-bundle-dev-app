<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Command;

use RZ\Roadiz\SolrBundle\ClientRegistryInterface;
use Solarium\Core\Client\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SolrCommand extends Command
{
    protected ?SymfonyStyle $io = null;

    public function __construct(
        protected readonly ClientRegistryInterface $clientRegistry,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        $this->setName('solr:check')
            ->addOption('client', null, InputOption::VALUE_REQUIRED, 'Solr client name to use', default: null)
            ->setDescription('Check Solr search engine server');
    }

    protected function validateSolrState(SymfonyStyle $io, ?string $clientName): ?Client
    {
        $client = $this->clientRegistry->getClient($clientName);

        if (null === $client) {
            $this->displayBasicConfig();

            return null;
        }

        $ping = $client->createPing();
        try {
            $client->ping($ping);
        } catch (\Exception) {
            $this->displayBasicConfig();

            return null;
        }

        return $client;
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        if (null === $this->validateSolrState($this->io, $input->getOption('client') ?: null)) {
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

        $this->io->error('Your Solr configuration is not valid.');
        $this->io->note(<<<EOD
Enable Solr in `config/packages/roadiz_core.yaml` file:

roadiz_core:
    solr:
        enabled: true

And edit your `config/packages/nelmio_solarium.yaml` file to enable Solr endpoint (example):

nelmio_solarium:
    endpoints:
        default:
            # We use Solr Cloud with collection
            host: '%env(SOLR_HOST)%'
            port: '%env(int:SOLR_PORT)%'
            path: '%env(SOLR_PATH)%'
            core: '%env(SOLR_CORE_NAME)%'
    clients:
        default:
            endpoints: [default]
            # You can customize the http timeout (in seconds) here. The default is 5sec.
            # adapter_timeout: 5

EOD);
    }
}
