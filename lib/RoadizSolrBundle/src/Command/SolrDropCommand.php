<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'solr:drop',
    description: 'Drop a Solr collection',
)]
final class SolrDropCommand extends Command
{
    public function __construct(
        protected readonly HttpClientInterface $client,
        private readonly string $solrHostname = 'solr',
        private readonly int $solrPort = 8983,
        private readonly string $solrCollectionName = 'roadiz',
        private readonly bool $solrSecure = false,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$input->isInteractive() || !$io->confirm('Are you sure you want to drop the Solr collection?', false)) {
            $io->warning('Operation cancelled');

            return Command::SUCCESS;
        }

        $protocol = $this->solrSecure ? 'https' : 'http';
        $baseUrl = $protocol.'://'.$this->solrHostname.':'.$this->solrPort;
        $response = $this->client->request('POST', $baseUrl.'/solr/admin/collections', [
            'query' => [
                'action' => 'DELETE',
                'name' => $this->solrCollectionName,
            ],
        ]);
        $statusCode = $response->getStatusCode();
        if (200 !== $statusCode) {
            $responseJson = \json_decode($response->getContent(false), true);
            if (
                \is_array($responseJson)
                && \is_array($responseJson['error'])
                && \is_string($responseJson['error']['msg'])
            ) {
                $io->warning($responseJson['error']['msg']);
            }

            return Command::FAILURE;
        }

        $io->success('Solr collection '.$this->solrCollectionName.' has been dropped');

        return Command::SUCCESS;
    }
}
