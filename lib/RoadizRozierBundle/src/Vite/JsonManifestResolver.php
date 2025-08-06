<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Vite;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

final readonly class JsonManifestResolver
{
    public function __construct(
        #[Autowire(param: 'roadiz_rozier.manifest_path')]
        private string $manifestPath,
        private Packages $packages,
        private CacheItemPoolInterface $cache,
        #[Autowire(param: 'kernel.debug')]
        private bool $debug = false,
    ) {
    }

    private function getManifest(): array
    {
        $cacheItem = $this->cache->getItem('roadiz_rozier.vite.manifest');
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        $manifestPath = $this->manifestPath;
        if ($this->debug) {
            $manifestPath = str_replace('manifest.json', 'manifest.dev.json', $this->manifestPath);
            if (!file_exists($manifestPath)) {
                // Keep build manifest path if dev manifest does not exist
                $manifestPath = $this->manifestPath;
            }
        }

        if (!file_exists($manifestPath)) {
            throw new \RuntimeException(sprintf('%s manifest not found', $manifestPath));
        }
        $cacheItem->set(\json_decode(
            (new Filesystem())->readFile($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR
        ));
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

    private function getBundlePrefixedPath(string $path): string
    {
        if (str_starts_with($path, 'http')) {
            // Already prefixed
            return $path;
        }

        // Ensure the path is prefixed with 'bundles/roadizrozier'
        return $this->packages->getUrl('/bundles/roadizrozier/'.$path);
    }

    public function getEntrypointCssFiles(string $name = 'main'): array
    {
        $entrypoint = $this->getEntrypoint($name);
        if (null !== $entrypoint && isset($entrypoint['css']) && is_array($entrypoint['css'])) {
            return array_map(fn ($cssFile) => $this->getBundlePrefixedPath($cssFile), $entrypoint['css']);
        }

        return [];
    }

    public function getEntrypointPreloadFiles(string $name = 'main'): array
    {
        $entrypoint = $this->getEntrypoint($name);
        if (null !== $entrypoint && isset($entrypoint['assets']) && is_array($entrypoint['assets'])) {
            return array_map(fn ($preloadFile) => [
                'href' => $this->getBundlePrefixedPath($preloadFile),
                // match file extension to determine the type
                'as' => match (pathinfo((string) $preloadFile, PATHINFO_EXTENSION)) {
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
                return [$this->getBundlePrefixedPath($entrypoint['file'])];
            }
            if (is_array($entrypoint['file'])) {
                return array_map(fn ($file) => $this->getBundlePrefixedPath($file), $entrypoint['file']);
            }
        }

        return [];
    }
}
