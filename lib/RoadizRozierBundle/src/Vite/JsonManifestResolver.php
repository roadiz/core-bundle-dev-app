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
        // Never use cache in debug mode: Vite dev server creates and removes
        // manifest.dev.json on the fly, a stale cache would pin the wrong asset origin.
        if (!$this->debug && $cacheItem->isHit()) {
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
        $manifest = \json_decode(
            (new Filesystem())->readFile($manifestPath),
            true,
            flags: JSON_THROW_ON_ERROR
        );
        if (!$this->debug) {
            $cacheItem->set($manifest);
            $this->cache->save($cacheItem);
        }

        return $manifest;
    }

    public function getEntrypoint(string $name = 'main'): ?array
    {
        $manifest = $this->getManifest();
        foreach ($manifest as $value) {
            if (is_array($value)
                && isset($value['isEntry'])
                && true === $value['isEntry']
                && $this->isNamed($value, $name)
            ) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Vite writes `name` for a script entry and only `names` (`<name>.css`) for a stylesheet entry.
     * The dev server manifest always sets `name`.
     */
    private function isNamed(array $entrypoint, string $name): bool
    {
        if (isset($entrypoint['name'])) {
            return $entrypoint['name'] === $name;
        }

        return isset($entrypoint['names'])
            && is_array($entrypoint['names'])
            && in_array($name.'.css', $entrypoint['names'], true);
    }

    /**
     * @return string[] the entry's own files: its script, or its stylesheet for a CSS entry
     */
    private function getEntrypointOwnFiles(?array $entrypoint): array
    {
        $file = $entrypoint['file'] ?? null;
        if (is_string($file)) {
            return [$file];
        }
        if (is_array($file)) {
            return array_values(array_filter($file, is_string(...)));
        }

        return [];
    }

    private function isStylesheet(string $path): bool
    {
        return 'css' === pathinfo((string) parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION);
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
        $files = array_filter($this->getEntrypointOwnFiles($entrypoint), $this->isStylesheet(...));
        if (isset($entrypoint['css']) && is_array($entrypoint['css'])) {
            $files = [...$files, ...$entrypoint['css']];
        }

        return array_values(array_map($this->getBundlePrefixedPath(...), $files));
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
        $files = array_filter(
            $this->getEntrypointOwnFiles($this->getEntrypoint($name)),
            fn (string $file) => !$this->isStylesheet($file),
        );

        return array_values(array_map($this->getBundlePrefixedPath(...), $files));
    }
}
