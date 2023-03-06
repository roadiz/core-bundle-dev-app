<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Realm;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\Realm;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Security\Authorization\Voter\RealmVoter;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Security;

final class RealmResolver implements RealmResolverInterface
{
    private ManagerRegistry $managerRegistry;
    private Security $security;

    public function __construct(ManagerRegistry $managerRegistry, Security $security)
    {
        $this->managerRegistry = $managerRegistry;
        $this->security = $security;
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
}
