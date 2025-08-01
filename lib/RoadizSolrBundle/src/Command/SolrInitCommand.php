<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Command;

use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'solr:init',
    description: 'Initialize a Solr collection with project schema fields. <info>Only with Solr Cloud</info>',
)]
final class SolrInitCommand extends Command
{
    public function __construct(
        protected readonly HttpClientInterface $client,
        protected readonly EventDispatcherInterface $eventDispatcher,
        private readonly string $solrHostname = 'solr',
        private readonly int $solrPort = 8983,
        private readonly string $solrCollectionName = 'roadiz',
        private readonly int $solrCollectionNumShards = 1,
        private readonly int $solrCollectionReplicationFactor = 2,
        private readonly bool $solrSecure = false,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(
            'numShards',
            's',
            InputOption::VALUE_REQUIRED,
            'Number of Shards',
            $this->solrCollectionNumShards
        );
        $this->addOption(
            'replicationFactor',
            'r',
            InputOption::VALUE_REQUIRED,
            'Replication Factor',
            $this->solrCollectionReplicationFactor
        );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $numShards = is_numeric($input->getOption('numShards'))
            ? intval($input->getOption('numShards'))
            : $this->solrCollectionNumShards;
        $replicationFactor = is_numeric($input->getOption('replicationFactor'))
            ? intval($input->getOption('replicationFactor'))
            : $this->solrCollectionReplicationFactor;

        $io->title(sprintf(
            'Solr Collection "%s" Initialization with %d shards, replication factors to %d',
            $this->solrCollectionName,
            $numShards,
            $replicationFactor,
        ));
        $protocol = $this->solrSecure ? 'https' : 'http';
        $baseUrl = $protocol.'://'.$this->solrHostname.':'.$this->solrPort;
        $response = $this->client->request('POST', $baseUrl.'/solr/admin/collections', [
            'query' => [
                'action' => 'CREATE',
                'name' => $this->solrCollectionName,
                'numShards' => $numShards,
                'replicationFactor' => $replicationFactor,
            ],
        ]);
        $statusCode = $response->getStatusCode();
        if (200 !== $statusCode) {
            $responseJson = \json_decode($response->getContent(false), true);
            if (
                \is_array($responseJson)
                && \is_array($responseJson['error'])
                && \is_string($responseJson['error']['msg'])
                && \str_starts_with($responseJson['error']['msg'], 'collection already exists')
            ) {
                $io->warning('Collection '.$this->solrCollectionName.' already exists, skipping creation');
            }
        }

        $io->title('Solr Collection Field types Initialization');
        $this->eventDispatcher->dispatch(new SolrInitializationEvent(
            $baseUrl,
            $this->solrCollectionName,
            $io
        ));

        $io->success('Solr collection '.$this->solrCollectionName.' has been initialized');

        return Command::SUCCESS;
    }
}
