<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\EventListener;

use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;

final readonly class DefaultSolrInitializationFieldsSubscriber extends AbstractSolrInitializationSubscriber
{
    #[\Override]
    public function onSolrInitialization(SolrInitializationEvent $event): void
    {
        /*
         * @see https://solr.apache.org/guide/solr/latest/indexing-guide/filters.html#ascii-folding-filter
         */
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

        $event->io->section('Replace frenchLightStem filter with frenchMinimalStem');
        $this->removeFilterFromFieldType($event->io, $event->baseUrl, $event->solrCollectionName, 'text_fr', [
            'name' => 'frenchLightStem',
        ]);
        $this->addFilterToFieldType($event->io, $event->baseUrl, $event->solrCollectionName, 'text_fr', [
            'name' => 'frenchMinimalStem',
        ]);

        $event->io->section('Adding DateRangeField field type');
        $this->requestSchemaApi($event->io, $event->baseUrl, $event->solrCollectionName, [
            'add-field-type' => [
                'name' => 'rdate',
                'class' => 'solr.DateRangeField',
            ],
        ]);

        $event->io->section('Adding *_dtr dynamic field');
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
