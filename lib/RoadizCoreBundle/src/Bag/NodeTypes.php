<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepositoryInterface;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileExistenceResource;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * @method NodeType|null get(string $key, $default = null)
 */
final class NodeTypes extends LazyParameterBag implements NodeTypeResolverInterface, CacheWarmerInterface
{
    private const string CACHE_FILE = '/roadiz/node_types.php';

    /**
     * Node-types are parsed from YAML files once, then exported as a PHP file in cache dir (OPcache friendly).
     * In debug mode, the file is regenerated whenever a node-type file changes.
     */
    public function __construct(
        private readonly NodeTypeRepositoryInterface $repository,
        #[Autowire(service: 'config_cache_factory')]
        private readonly ConfigCacheFactoryInterface $configCacheFactory,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
        #[Autowire(param: 'kernel.cache_dir')]
        private readonly string $cacheDir,
        private readonly string $nodeTypesDir,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function populateParameters(): void
    {
        $this->parameters = require $this->dump($this->cacheDir);
        $this->ready = true;
    }

    #[\Override]
    public function isOptional(): bool
    {
        // Invalid node-type files must not prevent the kernel from booting.
        return true;
    }

    #[\Override]
    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        return [$this->dump($cacheDir)];
    }

    private function dump(string $cacheDir): string
    {
        $cache = $this->configCacheFactory->cache($cacheDir.self::CACHE_FILE, function (ConfigCacheInterface $cache): void {
            $parameters = [];
            foreach ($this->repository->findAll() as $nodeType) {
                $parameters[$nodeType->getName()] = $nodeType;
                $parameters[$this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType)] = $nodeType;
            }
            $cache->write('<?php return '.VarExporter::export($parameters).';', [
                is_dir($this->nodeTypesDir) ? new DirectoryResource($this->nodeTypesDir) : new FileExistenceResource($this->nodeTypesDir),
            ]);
        });

        return $cache->getPath();
    }

    /**
     * @return array<int, NodeType>
     */
    #[\Override]
    public function all(?string $key = null): array
    {
        return array_values(array_unique(parent::all($key)));
    }

    #[\ReturnTypeWillChange]
    #[\Override]
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * @return array<int, NodeType>
     */
    public function allVisible(bool $visible = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isVisible() === $visible));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allReachable(bool $reachable = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isReachable() === $reachable));
    }

    /**
     * @return array<int, NodeType>
     */
    public function allPublishable(bool $publishable = true): array
    {
        return array_values(array_filter($this->all(), fn (NodeType $nodeType) => $nodeType->isPublishable() === $publishable));
    }
}
