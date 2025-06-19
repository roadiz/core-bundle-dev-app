<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Vite;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Asset\Package;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class JsonManifestResolver
{
    public function __construct(
        #[Autowire(param: 'roadiz_rozier.manifest_path')]
        private readonly string $manifestPath,
        #[Autowire(service: 'assets._default_package')]
        private readonly Package $rozierPackage,
        private CacheItemPoolInterface $cache,
    ) {
    }

    private function getManifest(): array
    {
        $cacheItem = $this->cache->getItem('roadiz_rozier.vite.manifest');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        if (!file_exists($this->manifestPath)) {
            throw new \RuntimeException(sprintf('%s manifest not found', $this->manifestPath));
        }
        $cacheItem->set(\json_decode(file_get_contents($this->manifestPath), true, flags: JSON_THROW_ON_ERROR));
        $this->cache->save($cacheItem);
        return $cacheItem->get();
    }

    public function getEntrypoint(string $name = 'main'): ?array
    {
        $manifest = $this->getManifest();
        foreach ($manifest as $value) {
            if (is_array($value)
                && isset($value['name'])
                && isset($value['isEntry'])
                && true === $value['isEntry']
                && $value['name'] === $name
            ) {
                return $value;
            }
        }
        return null;
    }

    public function getEntrypointCssFiles(string $name = 'main'): array
    {
        $entrypoint = $this->getEntrypoint($name);
        if (null !== $entrypoint && isset($entrypoint['css']) && is_array($entrypoint['css'])) {
            return array_map(fn ($cssFile) => $this->rozierPackage->getUrl($cssFile), $entrypoint['css']);
        }
        return [];
    }

    public function getEntrypointPreloadFiles(string $name = 'main'): array
    {
        $entrypoint = $this->getEntrypoint($name);
        if (null !== $entrypoint && isset($entrypoint['assets']) && is_array($entrypoint['assets'])) {
            return array_map(fn ($preloadFile) => [
                'href' => $this->rozierPackage->getUrl($preloadFile),
                // match file extension to determine the type
                'as' => match (pathinfo($preloadFile, PATHINFO_EXTENSION)) {
                    'js', 'mjs' => 'script',
                    'css' => 'style',
                    'png', 'jpg', 'webp', 'avif' => 'image',
                    'woff', 'woff2', 'ttf', 'otf' => 'font',
                    default => 'fetch',
                },
            ], $entrypoint['assets']);
        }
        return [];
    }

    public function getEntrypointScriptFiles(string $name = 'main'): array
    {
        $entrypoint = $this->getEntrypoint($name);
        if (null !== $entrypoint && isset($entrypoint['file'])) {
            if (is_string($entrypoint['file'])) {
                return [$this->rozierPackage->getUrl($entrypoint['file'])];
            }
            if (is_array($entrypoint['file'])) {
                return array_map(fn ($file) => $this->rozierPackage->getUrl($file), $entrypoint['file']);
            }
        }
        return [];
    }
}
