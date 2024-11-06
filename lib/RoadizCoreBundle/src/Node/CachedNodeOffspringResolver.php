<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\Core\AbstractEntities\NodeInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class CachedNodeOffspringResolver implements CachedNodeOffspringResolverInterface
{
    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws CacheException
     */
    public function getAllOffspringIds(NodeInterface $ancestor): array
    {
        $cacheItem = $this->cache->getItem(self::CACHE_PREFIX.$ancestor->getId());
        if (!$cacheItem->isHit()) {
            $nodeRepository = $this->managerRegistry->getRepository(Node::class);
            $offspringIds = $nodeRepository->findAllOffspringIdByNode($ancestor);
            $cacheItem->set($offspringIds);
            $cacheItem->expiresAfter(300);
            if ($cacheItem instanceof ItemInterface && $this->cache instanceof TagAwareCacheInterface) {
                $cacheItem->tag(array_map(function (int $nodeId) {
                    return self::CACHE_TAG_PREFIX.$nodeId;
                }, $offspringIds));
            }
            $this->cache->save($cacheItem);
        } else {
            $offspringIds = $cacheItem->get();
        }

        return $offspringIds;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function purgeOffspringCache(NodeInterface $node): void
    {
        $this->cache->deleteItem(self::CACHE_PREFIX.$node->getId());
        if ($this->cache instanceof TagAwareCacheInterface) {
            /*
             * If cache pool supports tags, we can invalidate all nodes at once.
             */
            $this->cache->invalidateTags([self::CACHE_TAG_PREFIX.$node->getId()]);
        } elseif ($node instanceof Node) {
            $ancestorsId = $this->managerRegistry
                ->getRepository(Node::class)
                ->findAllParentsIdByNode($node);
            foreach ($ancestorsId as $ancestorId) {
                $this->cache->deleteItem(self::CACHE_PREFIX.$ancestorId);
            }
        }
    }
}
