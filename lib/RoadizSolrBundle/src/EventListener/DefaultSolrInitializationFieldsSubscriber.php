<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use RZ\Roadiz\SolrBundle\SolrFrenchStemmerEnum;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class DefaultSolrInitializationFieldsSubscriber extends AbstractSolrInitializationSubscriber
{
    private const string FRENCH_OVERRIDES_RESOURCE = 'french_stemmer_overrides';

    /**
     * @param array<string, string> $frenchStemmerOverrides
     */
    public function __construct(
        HttpClientInterface $client,
        private SolrFrenchStemmerEnum $frenchStemmer = SolrFrenchStemmerEnum::MINIMAL,
        private bool $asciiFolding = true,
        private array $frenchStemmerOverrides = [],
    ) {
        parent::__construct($client);
    }

    #[\Override]
    public function onSolrInitialization(SolrInitializationEvent $event): void
    {
        /*
         * @see https://solr.apache.org/guide/solr/latest/indexing-guide/filters.html#ascii-folding-filter
         */
        if ($this->asciiFolding) {
            $event->io->section('Adding asciiFolding filter to localized text field types');
            $fieldTypes = [
                'text_de',
                'text_en',
                'text_fr',
                'text_it',
                'text_es',
            ];
            foreach ($fieldTypes as $fieldType) {
                $this->addFilterToFieldType($event->io, $event->baseUrl, $event->solrCollectionName, $fieldType, [
                    'name' => 'asciiFolding',
                    'preserveOriginal' => true,
                ]);
            }
        }

        $this->configureFrenchStemming($event);

        $event->io->section('Adding DateRangeField field type');
        if ($this->schemaElementExists($event, 'fieldtypes', 'rdate')) {
            $event->io->writeln('<info>rdate</info> field type already exists, skipping');
        } else {
            $this->requestSchemaApi($event->io, $event->baseUrl, $event->solrCollectionName, [
                'add-field-type' => [
                    'name' => 'rdate',
                    'class' => 'solr.DateRangeField',
                ],
            ]);
        }

        $event->io->section('Adding *_dtr dynamic field');
        if ($this->schemaElementExists($event, 'dynamicfields', '*_dtr')) {
            $event->io->writeln('<info>*_dtr</info> dynamic field already exists, skipping');
        } else {
            $this->requestSchemaApi($event->io, $event->baseUrl, $event->solrCollectionName, [
                'add-dynamic-field' => [
                    'name' => '*_dtr',
                    'type' => 'rdate',
                    'indexed' => true,
                    'stored' => true,
                ],
            ]);
        }
    }

    /**
     * @param 'fieldtypes'|'dynamicfields' $kind
     */
    private function schemaElementExists(SolrInitializationEvent $event, string $kind, string $name): bool
    {
        $response = $this->client->request(
            'GET',
            $event->baseUrl.'/solr/'.$event->solrCollectionName.'/schema/'.$kind.'/'.\rawurlencode($name)
        );

        return 200 === $response->getStatusCode();
    }

    /**
     * Rebuild text_fr stemming filters: [overrides] then [stemmer], always at the end of the chain
     * so that overrides and stemmer receive lowercased and ascii-folded tokens.
     */
    private function configureFrenchStemming(SolrInitializationEvent $event): void
    {
        $event->io->section('Configuring text_fr stemmer: '.$this->frenchStemmer->value);
        $fieldType = $this->getFieldType($event->io, $event->baseUrl, $event->solrCollectionName, 'text_fr', ['name' => 'text_fr']);
        if (null === $fieldType || !isset($fieldType['analyzer']['filters']) || !\is_array($fieldType['analyzer']['filters'])) {
            $event->io->warning('Field type text_fr not found or has no single analyzer, skipping stemmer configuration');

            return;
        }

        $filters = \array_values(\array_filter(
            $fieldType['analyzer']['filters'],
            fn (mixed $filter) => !$this->isFilter($filter, 'frenchLightStem')
                && !$this->isFilter($filter, 'frenchMinimalStem')
                && !$this->isFilter($filter, 'managedSynonymGraph')
        ));
        if ([] !== $this->frenchStemmerOverrides) {
            // ponytail: managed synonyms stand in for StemmerOverrideFilter, which needs a configset file
            // that the Schema API cannot upload. Fine for 1:1 single-word mappings only.
            $filters[] = ['name' => 'managedSynonymGraph', 'managed' => self::FRENCH_OVERRIDES_RESOURCE];
        }
        $stemmer = $this->frenchStemmer->getFilterName();
        if (null !== $stemmer) {
            $filters[] = ['name' => $stemmer];
        }
        $fieldType['analyzer']['filters'] = $filters;

        $this->requestSchemaApi($event->io, $event->baseUrl, $event->solrCollectionName, [
            'replace-field-type' => $fieldType,
        ]);

        if ([] === $this->frenchStemmerOverrides) {
            return;
        }
        $event->io->writeln('Uploading <info>'.\count($this->frenchStemmerOverrides).'</info> text_fr stemmer overrides');
        $resourceUrl = $event->baseUrl.'/solr/'.$event->solrCollectionName.'/schema/analysis/synonyms/'.self::FRENCH_OVERRIDES_RESOURCE;
        // Solr refuses to delete a resource used by the schema (403): remove stale words one by one instead.
        $current = \json_decode($this->client->request('GET', $resourceUrl)->getContent(false), true);
        $currentWords = \is_array($current) && \is_array($current['synonymMappings']['managedMap'] ?? null)
            ? \array_keys($current['synonymMappings']['managedMap'])
            : [];
        foreach (\array_diff($currentWords, \array_keys($this->frenchStemmerOverrides)) as $staleWord) {
            $this->client->request('DELETE', $resourceUrl.'/'.\rawurlencode((string) $staleWord))->getStatusCode();
        }
        $response = $this->client->request('PUT', $resourceUrl, [
            'json' => \array_map(fn (string $replacement) => [$replacement], $this->frenchStemmerOverrides),
        ]);
        if (200 !== $response->getStatusCode()) {
            $event->io->warning('Failed to upload stemmer overrides: '.$response->getContent(false));
        }
        // Applied by the core/collection reload done by solr:init once every subscriber ran.
    }
}
