<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\WebResponsePrefetchFixtures;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Doctrine\ORM\EntityManagerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;

/*
 * Covers NodesSourcesPrefetcher through web_response_by_path, on WebResponsePrefetchFixtures.
 */
final class WebResponsePrefetchTest extends ApiTestCase
{
    protected static ?bool $alwaysBootKernel = true;

    private const array VOLATILE_KEYS = ['createdAt', 'updatedAt'];

    private string $defaultTimezone;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        // Datetimes are hydrated in PHP default timezone: force it so the snapshot does not depend on the machine.
        $this->defaultTimezone = date_default_timezone_get();
        date_default_timezone_set('UTC');
        // RealmResolver caches denied realms per user in cache.app (Redis, shared across runs).
        static::getContainer()->get('cache.app')->clear();
    }

    #[\Override]
    protected function tearDown(): void
    {
        date_default_timezone_set($this->defaultTimezone);
        parent::tearDown();
    }

    /**
     * The snapshot was generated without prefetching: prefetched buffers must serialize the same.
     */
    public function testWebResponseIsIdenticalToTheOneWithoutPrefetching(): void
    {
        $data = $this->requestWebResponse(static::createClient(), $this->getPath('prefetch-page'));

        $actual = json_encode($this->scrub($data), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR)."\n";
        $file = __DIR__.'/snapshots/web_response_prefetch_page.json';
        if (!file_exists($file)) {
            file_put_contents($file, $actual);
            $this->markTestIncomplete(sprintf('Snapshot %s created, review it then run the test again.', $file));
        }

        // Key order depends on serializer versions, not on prefetching: compare content only.
        $this->assertSame(
            $this->sortKeys(json_decode((string) file_get_contents($file), true, flags: \JSON_THROW_ON_ERROR)),
            $this->sortKeys(json_decode($actual, true, flags: \JSON_THROW_ON_ERROR))
        );
    }

    public function testMediaReferencesAndUrlAliasesQueriesDoNotGrowWithBlocks(): void
    {
        $client = static::createClient();
        $threeBlocks = $this->countMediaReferencesAndUrlAliasesQueries($client, $this->getPath('prefetch-page'));
        $sevenBlocks = $this->countMediaReferencesAndUrlAliasesQueries($client, $this->getPath('prefetch-page-more'));

        $this->assertSame($threeBlocks, $sevenBlocks);
    }

    public function testReferencesHonourPublicationAndRealms(): void
    {
        $data = $this->requestWebResponse(static::createClient(), $this->getPath('prefetch-page'));

        $this->assertSame(['/prefetch-alias'], array_column($data['item']['nodeReferences'], 'url'));
        $this->assertSame(
            ['prefetch_second.jpg', 'prefetch_first.jpg'],
            array_column($data['item']['headerImage'], 'filename')
        );
    }

    public function testLocaleAwareServicesFollowItemTranslation(): void
    {
        $client = static::createClient();
        $source = $this->createTranslatedPage('fr');

        $translatorLocale = null;
        $translator = static::getContainer()->get('translator');
        $this->assertInstanceOf(LocaleAwareInterface::class, $translator);
        static::getContainer()->get('event_dispatcher')->addListener(
            KernelEvents::VIEW,
            function () use ($translator, &$translatorLocale) {
                $translatorLocale = $translator->getLocale();
            },
            100
        );

        $this->requestWebResponse($client, $this->generatePath($source));

        $this->assertSame('fr', $translatorLocale);
    }

    private function createTranslatedPage(string $locale): NodesSources
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $translation = $em->getRepository(Translation::class)->findOneBy(['locale' => $locale]);
        if (null === $translation) {
            $translation = (new Translation())->setName($locale)->setLocale($locale)->setAvailable(true);
            $em->persist($translation);
            $em->flush();
        }

        $pageType = static::getContainer()->get(NodeTypes::class)->get('Page');
        $this->assertNotNull($pageType);
        $source = static::getContainer()->get(UniqueNodeGenerator::class)->generate(
            nodeType: $pageType,
            translation: $translation,
        );
        $source->getNode()->setStatus(NodeStatus::PUBLISHED);
        $source->setPublishedAt(new \DateTime('2020-01-01 00:00:00'));
        $em->flush();

        return $source;
    }

    private function getPath(string $nodeName): string
    {
        $source = static::getContainer()->get(NodesSourcesRepository::class)->findOneBy(['node.nodeName' => $nodeName]);
        $this->assertNotNull($source, sprintf('Missing %s fixture (%s).', $nodeName, WebResponsePrefetchFixtures::class));

        return $this->generatePath($source);
    }

    private function generatePath(NodesSources $source): string
    {
        return static::getContainer()->get(UrlGeneratorInterface::class)->generate(RouteObjectInterface::OBJECT_BASED_ROUTE_NAME, [
            RouteObjectInterface::ROUTE_OBJECT => $source,
        ]);
    }

    /**
     * Other queries still grow with blocks: children and tags are queried per walker node.
     */
    private function countMediaReferencesAndUrlAliasesQueries(Client $client, string $path): int
    {
        $client->enableProfiler();
        $this->requestWebResponse($client, $path);
        $collector = $client->getProfile()?->getCollector('db');
        $this->assertInstanceOf(DoctrineDataCollector::class, $collector);

        return \count(array_filter(
            $collector->getQueries()['default'] ?? [],
            fn (array $query) => 1 === preg_match('#\bFROM (documents|nodes_to_nodes|url_aliases)\b#', $query['sql'])
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function requestWebResponse(Client $client, string $path): array
    {
        $response = $client->request('GET', '/api/web_response_by_path', [
            'query' => ['path' => $path],
        ]);
        $this->assertResponseIsSuccessful();

        return $response->toArray(false);
    }

    /**
     * Sorts object keys recursively, keeping list order.
     */
    private function sortKeys(mixed $data): mixed
    {
        if (!\is_array($data)) {
            return $data;
        }
        $data = array_map($this->sortKeys(...), $data);
        if (!array_is_list($data)) {
            ksort($data);
        }

        return $data;
    }

    /**
     * Removes timestamps, and replaces auto-increment ids and random JSON-LD genids,
     * which depend on database history.
     */
    private function scrub(mixed $data): mixed
    {
        if (\is_string($data)) {
            return preg_replace(
                ['#/\.well-known/genid/[0-9a-f]+#', '#(/api/[a-z_]+/)\d+#'],
                ['/.well-known/genid/{genid}', '$1{id}'],
                $data
            );
        }
        if (!\is_array($data)) {
            return $data;
        }

        $scrubbed = [];
        foreach (array_diff_key($data, array_flip(self::VOLATILE_KEYS)) as $key => $value) {
            $scrubbed[$key] = 'id' === $key && \is_int($value) ? '{id}' : $this->scrub($value);
        }

        return $scrubbed;
    }
}
