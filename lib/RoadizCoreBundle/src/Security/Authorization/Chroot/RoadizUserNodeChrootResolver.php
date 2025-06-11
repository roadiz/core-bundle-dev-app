<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Chroot;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\User;

/**
 * Classic Roadiz User chroot from Doctrine relation.
 */
final class RoadizUserNodeChrootResolver implements NodeChrootResolver
{
    #[\Override]
    public function supports(mixed $user): bool
    {
        return $user instanceof User;
    }

    /**
     * @param User $user
     */
    #[\Override]
    public function getChroot(mixed $user): ?Node
    {
        return $user->getChroot();
    }
}
