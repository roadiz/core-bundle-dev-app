<?php

declare(strict_types=1);

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use App\DataFixtures\WebResponsePrefetchFixtures;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodesToNodes;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Node\UniqueNodeGenerator;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
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

    public function testPreviewExposesUnpublishedReferences(): void
    {
        $user = (new User())->setUsername('prefetch-preview@test.test');
        $user->setUserRoles(['ROLE_BACKEND_USER']);
        $token = static::getContainer()->get(JWTTokenManagerInterface::class)->create($user);

        $response = static::createClient()->request('GET', '/api/web_response_by_path', [
            'query' => ['path' => $this->getPath('prefetch-page'), '_preview' => 1],
            'headers' => ['Authorization' => 'Bearer '.$token],
        ]);
        $this->assertResponseIsSuccessful();

        // Back-end users bypass DENY realms (NodesSourcesRepository::filterByDeniedRealms()).
        $this->assertSame(
            ['/prefetch-alias', '/prefetch-unpublished', '/prefetch-denied'],
            array_column($response->toArray(false)['item']['nodeReferences'], 'url')
        );
    }

    public function testBlocksHiddenByRealmAreNotLoaded(): void
    {
        $client = static::createClient();
        // Warm up realms, redirections and settings caches, which the first request fills.
        $this->requestWebResponse($client, $this->getPath('prefetch-hidden'));
        $oneBlock = $this->countQueries($client, $this->getPath('prefetch-hidden'));
        $threeBlocks = $this->countQueries($client, $this->getPath('prefetch-hidden-more'));

        // Walking blocks would issue children queries growing with blocks.
        $this->assertSame($oneBlock, $threeBlocks);
        $this->assertEmpty($this->requestWebResponse($client, $this->getPath('prefetch-hidden-more'))['blocks'] ?? []);
    }

    public function testCommonContentMenuQueriesDoNotGrowWithItems(): void
    {
        $client = static::createClient();
        $before = $this->countMediaReferencesAndUrlAliasesQueries($client, '/api/common_content');

        $this->addMenuItems();
        $after = $this->countMediaReferencesAndUrlAliasesQueries($client, '/api/common_content');

        $this->assertSame($before, $after);
    }

    public function testCommonContentMenuReferencesAndUrlAliases(): void
    {
        $response = static::createClient()->request('GET', '/api/common_content');
        $this->assertResponseIsSuccessful();
        $items = array_column($response->toArray(false)['menus']['prefetchMenuWalker']['children'], 'item');

        $links = array_filter($items, fn (array $item) => 'MenuLink' === $item['@type']);
        $this->assertGreaterThanOrEqual(2, \count($links));
        // Several links reference the same node: each one gets it, unpublished references stay excluded.
        foreach ($links as $link) {
            $this->assertSame(['/prefetch-alias'], array_column($link['linkInternalReference'], 'url'));
        }

        // Walker items fetch-join their URL aliases.
        $pages = array_column(array_filter($items, fn (array $item) => 'Page' === $item['@type']), 'url', 'title');
        $this->assertSame('/prefetch-menu/prefetch-menu-alias', $pages['prefetch-menu-page'] ?? null);
    }

    public function testLocaleAwareServicesFollowItemTranslation(): void
    {
        $client = static::createClient();
        $source = $this->createTranslatedPage('fr');
        $path = $this->generatePath($source);

        $translatorLocale = null;
        $requestLocale = null;
        $translator = static::getContainer()->get('translator');
        $this->assertInstanceOf(LocaleAwareInterface::class, $translator);
        // Before API Platform serialization, which builds blocks.
        static::getContainer()->get('event_dispatcher')->addListener(
            KernelEvents::VIEW,
            function (ViewEvent $event) use ($translator, &$translatorLocale, &$requestLocale) {
                $translatorLocale = $translator->getLocale();
                $requestLocale = $event->getRequest()->getLocale();
            },
            100
        );

        $data = $this->requestWebResponse($client, $path);

        $this->assertSame('fr', $translatorLocale);
        $this->assertSame('fr', $requestLocale);
        // Switching the RequestContext locale does not change generated URLs.
        $this->assertSame($path, $data['item']['url']);
    }

    /**
     * Adds a menu link referencing the same node, and a page with an URL alias, to the common content menu.
     */
    private function addMenuItems(): void
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $repository = $container->get(NodesSourcesRepository::class);
        $menu = $repository->findOneBy(['node.nodeName' => 'prefetch-menu']);
        $target = $repository->findOneBy(['node.nodeName' => 'prefetch-target']);
        $this->assertNotNull($menu);
        $this->assertNotNull($target);

        foreach (['MenuLink', 'Page'] as $nodeTypeName) {
            $nodeType = $container->get(NodeTypes::class)->get($nodeTypeName);
            $this->assertNotNull($nodeType);
            $source = $container->get(UniqueNodeGenerator::class)->generate(
                nodeType: $nodeType,
                translation: $menu->getTranslation(),
                parent: $menu->getNode(),
            );
            $source->getNode()->setStatus(NodeStatus::PUBLISHED)->setVisible(true);
            $source->setPublishedAt(new \DateTime('2020-01-01 00:00:00'));
            if ('MenuLink' === $nodeTypeName) {
                $em->persist((new NodesToNodes($source->getNode(), $target->getNode()))->setFieldName('link_internal_reference'));
            } else {
                $em->persist((new UrlAlias())->setAlias('prefetch-menu-alias-'.uniqid())->setNodeSource($source));
            }
        }
        $em->flush();
        $em->clear();
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
        return $this->countQueries($client, $path, '#\bFROM (documents|nodes_to_nodes|url_aliases)\b#');
    }

    /**
     * Counts queries of a web_response_by_path path, or of an API URL, optionally matching a SQL pattern.
     */
    private function countQueries(Client $client, string $path, ?string $sqlPattern = null): int
    {
        $client->enableProfiler();
        if (str_starts_with($path, '/api/')) {
            $client->request('GET', $path);
            $this->assertResponseIsSuccessful();
        } else {
            $this->requestWebResponse($client, $path);
        }
        $collector = $client->getProfile()?->getCollector('db');
        $this->assertInstanceOf(DoctrineDataCollector::class, $collector);

        return \count(array_filter(
            $collector->getQueries()['default'] ?? [],
            fn (array $query) => null === $sqlPattern || 1 === preg_match($sqlPattern, $query['sql'])
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
