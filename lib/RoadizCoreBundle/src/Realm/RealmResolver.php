<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Realm;

use Doctrine\Persistence\ManagerRegistry;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Bundle\SecurityBundle\Security;

final class RealmResolver implements RealmResolverInterface
{
    private ManagerRegistry $managerRegistry;
    private Security $security;
    private CacheItemPoolInterface $cache;

    public function __construct(ManagerRegistry $managerRegistry, Security $security, CacheItemPoolInterface $cache)
    {
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
        $this->cache = $cache;
    }

    public function getRealms(?Node $node): array
    {
        if (null === $node) {
            return [];
        }
        return $this->managerRegistry->getRepository(Realm::class)->findByNode($node);
    }

    public function isGranted(RealmInterface $realm): bool
    {
        return $this->security->isGranted(RealmVoter::READ, $realm);
    }

    public function denyUnlessGranted(RealmInterface $realm): void
    {
        if (!$this->isGranted($realm)) {
            throw new UnauthorizedHttpException(
                $realm->getChallenge(),
                'WebResponse was denied by Realm authorization, check Www-Authenticate header'
            );
        }
    }

    public function getGrantedRealms(): array
    {
        $cacheItem = $this->cache->getItem('granted_realms');
        if (!$cacheItem->isHit()) {
            $allRealms = $this->managerRegistry->getRepository(Realm::class)->findBy([]);
            $cacheItem->set(array_filter($allRealms, fn(RealmInterface $realm) => $this->isGranted($realm)));
            $cacheItem->expiresAfter(new \DateInterval('PT1H'));
            $this->cache->save($cacheItem);
        }
        return $cacheItem->get();
    }

    public function getDeniedRealms(): array
    {
        $cacheItem = $this->cache->getItem('denied_realms');
        if (!$cacheItem->isHit()) {
            $allRealms = $this->managerRegistry->getRepository(Realm::class)->findBy([]);
            $cacheItem->set(array_filter($allRealms, fn(RealmInterface $realm) => !$this->isGranted($realm)));
            $cacheItem->expiresAfter(new \DateInterval('PT1H'));
            $this->cache->save($cacheItem);
        }
        return $cacheItem->get();
    }
}
