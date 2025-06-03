<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Authorization\Voter;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class SuperAdminRoleHierarchyVoter extends RoleArrayVoter
{
    public function __construct(private readonly ManagerRegistry $managerRegistry, string $prefix = 'ROLE_')
    {
        parent::__construct($prefix);
    }

    #[\Override]
    protected function extractRoles(TokenInterface $token): array
    {
        $roleNames = parent::extractRoles($token);
        if ($this->isSuperAdmin($token)) {
            $roleNames = array_merge(
                $roleNames,
                $this->managerRegistry->getRepository(Role::class)->getAllBasicRoleName()
            );
        }

        return $roleNames;
    }

    private function isSuperAdmin(TokenInterface $token): bool
    {
        $roleNames = parent::extractRoles($token);

        return
            \in_array('ROLE_SUPER_ADMIN', $roleNames)
            || \in_array('ROLE_SUPERADMIN', $roleNames)
        ;
    }
}
