<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use RZ\Roadiz\SolrBundle\EventListener\AbstractSolrInitializationSubscriber;
use Symfony\Component\HttpClient\MockHttpClient;

final class SolrInitializationFilterMatchingTest extends TestCase
{
    /**
     * @dataProvider filterProvider
     */
    public function testMatchesSchemaApiAndManagedSchemaXmlDeclarations(mixed $filter, string $filterName, bool $expected): void
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

        $this->assertSame($expected, $subscriber->matches($filter, $filterName));
    }

    public static function filterProvider(): array
    {
        return [
            'schema api name' => [['name' => 'frenchMinimalStem'], 'frenchMinimalStem', true],
            'managed-schema.xml class' => [['class' => 'solr.FrenchMinimalStemFilterFactory'], 'frenchMinimalStem', true],
            'class with options' => [['class' => 'solr.ASCIIFoldingFilterFactory', 'preserveOriginal' => 'true'], 'asciiFolding', true],
            'managed synonyms class' => [['class' => 'solr.ManagedSynonymGraphFilterFactory'], 'managedSynonymGraph', true],
            'other stemmer class' => [['class' => 'solr.FrenchLightStemFilterFactory'], 'frenchMinimalStem', false],
            'other filter name' => [['name' => 'lowercase'], 'asciiFolding', false],
            'not an array' => ['asciiFolding', 'asciiFolding', false],
            'empty filter' => [[], 'asciiFolding', false],
        ];
    }
}
