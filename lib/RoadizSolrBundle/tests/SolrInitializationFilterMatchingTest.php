<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use RZ\Roadiz\SolrBundle\EventListener\AbstractSolrInitializationSubscriber;
use Symfony\Component\HttpClient\MockHttpClient;

final class SolrInitializationFilterMatchingTest extends TestCase
{
    public function testMatchesSchemaApiAndManagedSchemaXmlDeclarations(): void
    {
        $subscriber = new readonly class(new MockHttpClient()) extends AbstractSolrInitializationSubscriber {
            #[\Override]
            public function onSolrInitialization(SolrInitializationEvent $event): void
            {
            }

            public function matches(mixed $filter, string $filterName): bool
            {
                return $this->isFilter($filter, $filterName);
            }
        };

        // Schema API style
        $this->assertTrue($subscriber->matches(['name' => 'frenchMinimalStem'], 'frenchMinimalStem'));
        // managed-schema.xml style (e.g. custom Solr images)
        $this->assertTrue($subscriber->matches(['class' => 'solr.FrenchMinimalStemFilterFactory'], 'frenchMinimalStem'));
        $this->assertTrue($subscriber->matches(['class' => 'solr.ASCIIFoldingFilterFactory', 'preserveOriginal' => 'true'], 'asciiFolding'));
        $this->assertTrue($subscriber->matches(['class' => 'solr.ManagedSynonymGraphFilterFactory'], 'managedSynonymGraph'));

        $this->assertFalse($subscriber->matches(['class' => 'solr.FrenchLightStemFilterFactory'], 'frenchMinimalStem'));
        $this->assertFalse($subscriber->matches(['name' => 'lowercase'], 'asciiFolding'));
        $this->assertFalse($subscriber->matches('asciiFolding', 'asciiFolding'));
        $this->assertFalse($subscriber->matches([], 'asciiFolding'));
    }
}
