<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract readonly class AbstractSolrInitializationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected HttpClientInterface $client,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            SolrInitializationEvent::class => ['onSolrInitialization', 0],
        ];
    }

    abstract public function onSolrInitialization(SolrInitializationEvent $event): void;

    protected function requestSchemaApi(SymfonyStyle $io, string $baseUrl, string $solrCollectionName, array $jsonArray): void
    {
        $response = $this->client->request('POST', $baseUrl.'/api/collections/'.$solrCollectionName.'/schema', [
            'json' => $jsonArray,
        ]);
        // trigger lazy request
        if (200 !== $response->getStatusCode()) {
            $io->warning('Failed to update schema: '.$response->getContent(false));
        }
    }

    protected function addFilterToFieldType(SymfonyStyle $io, string $baseUrl, string $solrCollectionName, string $fieldType, array $filterConfig): void
    {
        $filterName = $filterConfig['name'];
        $io->writeln('Adding <info>'.$filterName.'</info> filter to <info>'.$fieldType.'</info> field type');

        $newFieldType = $this->getFieldType($io, $baseUrl, $solrCollectionName, $fieldType, $filterConfig);
        if (null === $newFieldType) {
            $io->warning('Field type '.$fieldType.' not found, skipping filter addition');

            return;
        }
        /*
         * Try to add the ASCII Folding Filter to existing analyzer or queryAnalyzer
         */
        if (isset($newFieldType['analyzer'])) {
            foreach ($newFieldType['analyzer']['filters'] as $filter) {
                if (\is_array($filter) && isset($filter['name']) && $filterName === $filter['name']) {
                    $io->warning($filterName.' filter already exists in '.$fieldType.' field type, skipping');

                    return;
                }
            }
            $newFieldType['analyzer']['filters'][] = $filterConfig;
        } elseif (isset($newFieldType['queryAnalyzer'])) {
            foreach ($newFieldType['queryAnalyzer']['filters'] as $filter) {
                if (\is_array($filter) && isset($filter['name']) && $filterName === $filter['name']) {
                    $io->warning($filterName.' filter already exists in '.$fieldType.' field type, skipping');

                    return;
                }
            }
            $newFieldType['queryAnalyzer']['filters'][] = $filterConfig;
        }

        $this->requestSchemaApi($io, $baseUrl, $solrCollectionName, [
            'replace-field-type' => $newFieldType,
        ]);
    }

    protected function removeFilterFromFieldType(SymfonyStyle $io, string $baseUrl, string $solrCollectionName, string $fieldType, array $filterConfig): void
    {
        $filterName = $filterConfig['name'];
        $io->writeln('Removing <info>'.$filterName.'</info> filter from <info>'.$fieldType.'</info> field type');

        $newFieldType = $this->getFieldType($io, $baseUrl, $solrCollectionName, $fieldType, $filterConfig);
        if (null === $newFieldType) {
            $io->warning('Field type '.$fieldType.' not found, skipping filter removal');

            return;
        }

        if (isset($newFieldType['analyzer'])) {
            foreach ($newFieldType['analyzer']['filters'] as $index => $filter) {
                if (\is_array($filter) && isset($filter['name']) && $filterName === $filter['name']) {
                    unset($newFieldType['analyzer']['filters'][$index]);
                }
            }
            $newFieldType['analyzer']['filters'] = \array_values($newFieldType['analyzer']['filters'] ?? []);
        } elseif (isset($newFieldType['queryAnalyzer'])) {
            foreach ($newFieldType['queryAnalyzer']['filters'] as $index => $filter) {
                if (\is_array($filter) && isset($filter['name']) && $filterName === $filter['name']) {
                    unset($newFieldType['queryAnalyzer']['filters'][$index]);
                }
            }
            $newFieldType['queryAnalyzer']['filters'] = \array_values($newFieldType['queryAnalyzer']['filters'] ?? []);
        }

        $this->requestSchemaApi($io, $baseUrl, $solrCollectionName, [
            'replace-field-type' => $newFieldType,
        ]);
    }

    protected function getFieldType(SymfonyStyle $io, string $baseUrl, string $solrCollectionName, string $fieldType, array $filterConfig): ?array
    {
        if (!isset($filterConfig['name'])) {
            throw new \InvalidArgumentException('The filter configuration must have a "name" key');
        }

        $response = $this->client->request('GET', $baseUrl.'/api/collections/'.$solrCollectionName.'/schema/fieldtypes/'.$fieldType);
        if (200 !== $response->getStatusCode()) {
            return null;
        }
        $responseJson = \json_decode($response->getContent(), true);
        if (!\is_array($responseJson) || !\is_array($responseJson['fieldType'])) {
            return null;
        }

        return $responseJson['fieldType'];
    }
}
