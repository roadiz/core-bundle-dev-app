<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\SolrBundle\Event\SolrInitializationEvent;
use RZ\Roadiz\SolrBundle\EventListener\DefaultSolrInitializationFieldsSubscriber;
use RZ\Roadiz\SolrBundle\SolrFrenchStemmerEnum;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class DefaultSolrInitializationFieldsSubscriberTest extends TestCase
{
    private const array OWN_OVERRIDES = ['name' => 'managedSynonymGraph', 'managed' => 'french_stemmer_overrides'];
    private const array PROJECT_SYNONYMS = ['class' => 'solr.ManagedSynonymGraphFilterFactory', 'managed' => 'project_synonyms'];

    /**
     * @dataProvider textFrFiltersProvider
     */
    public function testRebuildsTextFrStemmingFilters(
        array $currentFilters,
        SolrFrenchStemmerEnum $stemmer,
        array $overrides,
        array $expectedFilters,
    ): void {
        $replacedFilters = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use ($currentFilters, &$replacedFilters) {
            if ('GET' === $method && str_ends_with($url, '/schema/fieldtypes/text_fr')) {
                return new MockResponse(\json_encode(['fieldType' => [
                    'name' => 'text_fr',
                    'analyzer' => ['filters' => $currentFilters],
                ]], JSON_THROW_ON_ERROR));
            }
            if ('POST' === $method && str_ends_with($url, '/schema')) {
                $body = \json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
                if (isset($body['replace-field-type'])) {
                    $replacedFilters = $body['replace-field-type']['analyzer']['filters'];
                }
            }

            return new MockResponse('{}');
        });

        $subscriber = new DefaultSolrInitializationFieldsSubscriber($client, $stemmer, false, $overrides);
        $subscriber->onSolrInitialization(new SolrInitializationEvent(
            'http://solr:8983',
            'roadiz',
            new SymfonyStyle(new ArrayInput([]), new NullOutput()),
        ));

        $this->assertSame($expectedFilters, $replacedFilters);
    }

    public static function textFrFiltersProvider(): array
    {
        $base = [['name' => 'lowercase'], ['name' => 'asciiFolding', 'preserveOriginal' => true]];

        return [
            'default schema: light replaced by minimal' => [
                [['name' => 'lowercase'], ['name' => 'frenchLightStem'], ['name' => 'asciiFolding', 'preserveOriginal' => true]],
                SolrFrenchStemmerEnum::MINIMAL,
                [],
                [...$base, ['name' => 'frenchMinimalStem']],
            ],
            'already initialized: unchanged' => [
                [...$base, ['name' => 'frenchMinimalStem']],
                SolrFrenchStemmerEnum::MINIMAL,
                [],
                [...$base, ['name' => 'frenchMinimalStem']],
            ],
            'managed-schema.xml class declarations' => [
                [['name' => 'lowercase'], ['class' => 'solr.FrenchLightStemFilterFactory'], ['name' => 'asciiFolding', 'preserveOriginal' => true]],
                SolrFrenchStemmerEnum::MINIMAL,
                [],
                [...$base, ['name' => 'frenchMinimalStem']],
            ],
            'overrides added before stemmer' => [
                [...$base, ['name' => 'frenchMinimalStem']],
                SolrFrenchStemmerEnum::MINIMAL,
                ['chateaux' => 'chateau'],
                [...$base, self::OWN_OVERRIDES, ['name' => 'frenchMinimalStem']],
            ],
            'own overrides removed when config is empty' => [
                [...$base, self::OWN_OVERRIDES, ['name' => 'frenchMinimalStem']],
                SolrFrenchStemmerEnum::MINIMAL,
                [],
                [...$base, ['name' => 'frenchMinimalStem']],
            ],
            'project managed synonyms are kept' => [
                [['name' => 'lowercase'], self::PROJECT_SYNONYMS, ['name' => 'asciiFolding', 'preserveOriginal' => true], ['name' => 'frenchLightStem']],
                SolrFrenchStemmerEnum::MINIMAL,
                [],
                [['name' => 'lowercase'], self::PROJECT_SYNONYMS, ['name' => 'asciiFolding', 'preserveOriginal' => true], ['name' => 'frenchMinimalStem']],
            ],
            'stemming disabled' => [
                [...$base, ['name' => 'frenchMinimalStem']],
                SolrFrenchStemmerEnum::NONE,
                [],
                $base,
            ],
        ];
    }
}
