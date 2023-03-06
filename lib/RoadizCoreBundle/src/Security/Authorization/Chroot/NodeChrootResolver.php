<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Chroot;

use RZ\Roadiz\CoreBundle\Entity\Node;

/**
 * Contract to resolver a Node chroot to lock any User type into a part
 * of a Roadiz node-tree.
 *
 * This enables third-party User from OAuth2 or SSO to be locked using their
 * own business logic, without need of a Roadiz User.
 *
 * @package RZ\Roadiz\CoreBundle\Security\Authorization\Chroot
 */
interface NodeChrootResolver
{
    /**
     * @param mixed $user
     *
     * @return bool
     */
    public function supports($user): bool;

    /**
     * @param mixed $user
     *
     * @return Node|null
     */
    public function getChroot($user): ?Node;
}
