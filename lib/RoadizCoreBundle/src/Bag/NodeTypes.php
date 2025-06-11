<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Bag;

use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Bag\LazyParameterBag;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepositoryInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @method NodeType|null get(string $key, $default = null)
 */
final class NodeTypes extends LazyParameterBag implements NodeTypeResolverInterface
{
    public function __construct(
        private readonly NodeTypeRepositoryInterface $repository,
        private readonly CacheItemPoolInterface $cacheItemPool,
        #[Autowire(param: 'kernel.debug')]
        private readonly bool $debug,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function populateParameters(): void
    {
        $cacheItem = null;
        if (!$this->debug) {
            $cacheItem = $this->cacheItemPool->getItem('node_types_bag');
            if ($cacheItem->isHit()) {
                $this->parameters = $cacheItem->get();
                $this->ready = true;

                return;
            }
        }

        $nodeTypes = $this->repository->findAll();
        $this->parameters = [];
        foreach ($nodeTypes as $nodeType) {
            $this->parameters[$nodeType->getName()] = $nodeType;
            $this->parameters[$nodeType->getSourceEntityFullQualifiedClassName()] = $nodeType;
        }

        if (!$this->debug && isset($cacheItem)) {
            $cacheItem->set($this->parameters);
            $this->cacheItemPool->save($cacheItem);
        }

        $this->ready = true;
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
