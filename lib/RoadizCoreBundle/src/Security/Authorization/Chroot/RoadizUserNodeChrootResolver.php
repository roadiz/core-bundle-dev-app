<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Chroot;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\User;

/**
 * Classic Roadiz User chroot from Doctrine relation.
 *
 * @package RZ\Roadiz\CoreBundle\Security\Authorization\Chroot
 */
class RoadizUserNodeChrootResolver implements NodeChrootResolver
{
    public function supports($user): bool
    {
        return $user instanceof User;
    }

    /**
     * @param User $user
     *
     * @return Node|null
     */
    public function getChroot($user): ?Node
    {
        return $user->getChroot();
    }
}
