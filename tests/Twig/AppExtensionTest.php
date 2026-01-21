<?php

declare(strict_types=1);

namespace App\Tests\Twig;

use App\Twig\AppExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;

final class AppExtensionTest extends TestCase
{
    public function testJsonDecodeFilterDecodesFeatureCollection(): void
    {
        $json = '{"type":"FeatureCollection","features":[{"type":"Feature","properties":{"name":"Paris"},"geometry":{"type":"Point","coordinates":[2.3522,48.8566]}}]}';
        $ext = new AppExtension(
            // Provide minimal stubs for constructor deps; they are unused by the filter
            requestStack: new \Symfony\Component\HttpFoundation\RequestStack(),
            managerRegistry: $this->createMock(\Doctrine\Persistence\ManagerRegistry::class),
            previewResolver: $this->createMock(\RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface::class),
            treeWalkerGenerator: $this->createMock(\RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator::class),
        );

        $filters = $ext->getFilters();
        $filter = $this->findFilter($filters, 'json_decode');
        $callable = $filter->getCallable();
        $decoded = $callable($json);

        $this->assertIsArray($decoded);
        $this->assertSame('FeatureCollection', $decoded['type'] ?? null);
        $this->assertSame('Paris', $decoded['features'][0]['properties']['name'] ?? null);
        $this->assertSame([2.3522, 48.8566], $decoded['features'][0]['geometry']['coordinates'] ?? null);
    }

    public function testJsonDecodeFilterHandlesInvalidJsonGracefully(): void
    {
        $ext = new AppExtension(
            requestStack: new \Symfony\Component\HttpFoundation\RequestStack(),
            managerRegistry: $this->createMock(\Doctrine\Persistence\ManagerRegistry::class),
            previewResolver: $this->createMock(\RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface::class),
            treeWalkerGenerator: $this->createMock(\RZ\Roadiz\CoreBundle\Api\TreeWalker\TreeWalkerGenerator::class),
        );

        $filters = $ext->getFilters();
        $filter = $this->findFilter($filters, 'json_decode');
        $callable = $filter->getCallable();

        $this->assertNull($callable('not json'));
        $this->assertNull($callable(''));
        $this->assertNull($callable(null));
    }

    /** @param TwigFilter[] $filters */
    private function findFilter(array $filters, string $name): TwigFilter
    {
        foreach ($filters as $filter) {
            if ($filter->getName() === $name) {
                return $filter;
            }
        }
        $this->fail("Filter '$name' not found");
    }
}
