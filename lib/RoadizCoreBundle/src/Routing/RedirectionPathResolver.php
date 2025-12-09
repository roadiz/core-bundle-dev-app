<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Entity\Redirection;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class RedirectionPathResolver implements PathResolverInterface
{
    public const string CACHE_KEY = 'redirection_path_resolver_cache';

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private CacheItemPoolInterface $cacheAdapter,
        private Stopwatch $stopwatch,
    ) {
    }

    /**
     * @return array<string, int> All redirections with query as key, and ID as value
     */
    private function getRedirectionHashMap(): array
    {
        $cacheItem = $this->cacheAdapter->getItem(self::CACHE_KEY);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }
        // Populate cache item
        /** @var array[] $redirectionEntities */
        $redirectionEntities = $this->managerRegistry
            ->getRepository(Redirection::class)
            ->createQueryBuilder('r')
            ->select(['r.id', 'r.query'])
            ->getQuery()
            ->getArrayResult();

        $redirections = [];
        foreach ($redirectionEntities as $redirection) {
            $redirections[$redirection['query']] = $redirection['id'];
        }
        ksort($redirections);
        $cacheItem->set($redirections);
        $this->cacheAdapter->save($cacheItem);

        return $redirections;
    }

    #[\Override]
    public function resolvePath(
        string $path,
        array $supportedFormatExtensions = ['html'],
        bool $allowRootPaths = false,
        bool $allowNonReachableNodes = true,
    ): ResourceInfo {
        $this->stopwatch->start('lookForRedirection', 'routing');
        $redirectionId = $this->getRedirectionHashMap()[$path] ?? null;
        $this->stopwatch->stop('lookForRedirection');

        if (null === $redirectionId) {
            throw new ResourceNotFoundException(sprintf('%s did not match any cached Redirection', $path));
        }

        $this->stopwatch->start('findRedirection', 'routing');
        $redirection = $this->managerRegistry
            ->getRepository(Redirection::class)
            ->find($redirectionId);
        $this->stopwatch->stop('findRedirection');

        if (null === $redirection) {
            throw new ResourceNotFoundException(sprintf('%s did not match any Doctrine Redirection', $path));
        }

        $this->stopwatch->start('incrementRedirection', 'routing');
        $redirection->incrementUseCount();
        $this->managerRegistry->getManagerForClass(Redirection::class)?->flush();
        $this->stopwatch->stop('incrementRedirection');

        return (new ResourceInfo())->setResource($redirection);
    }
}
