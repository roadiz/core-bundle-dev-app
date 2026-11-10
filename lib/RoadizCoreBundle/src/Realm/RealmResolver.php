<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Realm;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Contracts\Service\ResetInterface;

/**
 * getGrantedRealms()/getDeniedRealms()/hasRealms()/hasRealmsWithSerializationGroup()
 * memoize their result for the lifetime of this instance: the "app" cache pool is a
 * raw Redis adapter with no in-request layer in front of it, so without this, every
 * caller (NodesSourcesRepository now calls getDeniedRealms() on every query) would
 * pay a Redis round-trip each time instead of once. ResetInterface clears the memo
 * between requests/messages so long-lived Messenger workers don't serve stale grants
 * for the process lifetime.
 */
final class RealmResolver implements RealmResolverInterface, ResetInterface
{
    private ?array $grantedRealms = null;
    private ?array $deniedRealms = null;
    private ?bool $hasRealms = null;
    private ?bool $hasRealmsWithSerializationGroup = null;

    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly Security $security,
        private readonly CacheItemPoolInterface $cache,
    ) {
    }

    #[\Override]
    public function getRealms(?Node $node): array
    {
        if (null === $node) {
            return [];
        }

        return $this->managerRegistry->getRepository(Realm::class)->findByNode($node);
    }

    #[\Override]
    public function getRealmsWithSerializationGroup(?Node $node): array
    {
        if (null === $node) {
            return [];
        }

        return $this->managerRegistry->getRepository(Realm::class)->findByNodeWithSerializationGroup($node);
    }

    #[\Override]
    public function isGranted(RealmInterface $realm): bool
    {
        return $this->security->isGranted(RealmVoter::READ, $realm);
    }

    #[\Override]
    public function denyUnlessGranted(RealmInterface $realm): void
    {
        if (!$this->isGranted($realm)) {
            throw new UnauthorizedHttpException($realm->getChallenge(), 'WebResponse was denied by Realm authorization, check Www-Authenticate header');
        }
    }

    private function getUserCacheKey(): string
    {
        return (new AsciiSlugger())
            ->slug($this->security->getUser()?->getUserIdentifier() ?? 'anonymous')
            ->__toString();
    }

    #[\Override]
    public function getGrantedRealms(): array
    {
        if (null !== $this->grantedRealms) {
            return $this->grantedRealms;
        }

        $cacheItem = $this->cache->getItem('granted_realms_'.$this->getUserCacheKey());
        if (!$cacheItem->isHit()) {
            $allRealms = $this->managerRegistry->getRepository(Realm::class)->findBy([]);
            $cacheItem->set(array_values(array_filter($allRealms, $this->isGranted(...))));
            $cacheItem->expiresAfter(new \DateInterval('PT1H'));
            $this->cache->save($cacheItem);
        }

        return $this->grantedRealms = $cacheItem->get();
    }

    #[\Override]
    public function getDeniedRealms(): array
    {
        if (null !== $this->deniedRealms) {
            return $this->deniedRealms;
        }

        $cacheItem = $this->cache->getItem('denied_realms_'.$this->getUserCacheKey());
        if (!$cacheItem->isHit()) {
            $allRealms = $this->managerRegistry->getRepository(Realm::class)->findBy([]);
            $cacheItem->set(array_values(array_filter($allRealms, fn (RealmInterface $realm) => !$this->isGranted($realm))));
            $cacheItem->expiresAfter(new \DateInterval('PT1H'));
            $this->cache->save($cacheItem);
        }

        return $this->deniedRealms = $cacheItem->get();
    }

    #[\Override]
    public function hasRealms(): bool
    {
        if (null !== $this->hasRealms) {
            return $this->hasRealms;
        }

        $cacheItem = $this->cache->getItem('app_has_realms');
        if (!$cacheItem->isHit()) {
            $hasRealms = $this->managerRegistry->getRepository(Realm::class)->countBy([]) > 0;
            $cacheItem->set($hasRealms);
            $cacheItem->expiresAfter(new \DateInterval('PT2H'));
            $this->cache->save($cacheItem);
        }

        return $this->hasRealms = $cacheItem->get();
    }

    #[\Override]
    public function hasRealmsWithSerializationGroup(): bool
    {
        if (null !== $this->hasRealmsWithSerializationGroup) {
            return $this->hasRealmsWithSerializationGroup;
        }

        $cacheItem = $this->cache->getItem('app_has_realms_with_serialization_group');
        if (!$cacheItem->isHit()) {
            $hasRealms = $this->managerRegistry->getRepository(Realm::class)->countWithSerializationGroup() > 0;
            $cacheItem->set($hasRealms);
            $cacheItem->expiresAfter(new \DateInterval('PT2H'));
            $this->cache->save($cacheItem);
        }

        return $this->hasRealmsWithSerializationGroup = $cacheItem->get();
    }

    #[\Override]
    public function reset(): void
    {
        $this->grantedRealms = null;
        $this->deniedRealms = null;
        $this->hasRealms = null;
        $this->hasRealmsWithSerializationGroup = null;
    }
}
