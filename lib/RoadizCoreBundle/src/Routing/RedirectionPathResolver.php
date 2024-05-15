<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Stopwatch\Stopwatch;

final class RedirectionPathResolver implements PathResolverInterface
{
    public const CACHE_KEY = 'redirection_path_resolver_cache';

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly CacheItemPoolInterface $cacheAdapter,
        private readonly Stopwatch $stopwatch,
    ) {
    }

    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true
    ): ResourceInfo {
        $this->stopwatch->start('lookForRedirection', 'routing');
        $cacheItem = $this->cacheAdapter->getItem(self::CACHE_KEY);
        if (!$cacheItem->isHit()) {
            // Populate cache item
            /** @var array[] $redirections */
            $redirections = $this->managerRegistry
                ->getRepository(Redirection::class)
                ->createQueryBuilder('r')
                ->select(['r.id', 'r.query'])
                ->getQuery()
                ->getArrayResult();
            $redirections = array_combine(
                array_column($redirections, 'query'),
                array_column($redirections, 'id')
            );
            $cacheItem->set($redirections);
            $this->cacheAdapter->save($cacheItem);
        } else {
            /** @var array[] $redirections */
            $redirections = $cacheItem->get();
        }

        /** @var int|null $redirectionId */
        $redirectionId = $redirections[$path] ?? null;
        $this->stopwatch->stop('lookForRedirection');

        if (null === $redirectionId) {
            throw new ResourceNotFoundException();
        }
        $this->stopwatch->start('findRedirection', 'routing');
        $redirection = $this->managerRegistry
            ->getRepository(Redirection::class)
            ->find($redirectionId);
        $this->stopwatch->stop('findRedirection');
        if (null === $redirection) {
            throw new ResourceNotFoundException();
        }

        $this->stopwatch->start('incrementRedirection', 'routing');
        $redirection->incrementUseCount();
        $this->managerRegistry->getManagerForClass(Redirection::class)->flush();
        $this->stopwatch->stop('incrementRedirection');

        return (new ResourceInfo())->setResource($redirection);
    }
}
