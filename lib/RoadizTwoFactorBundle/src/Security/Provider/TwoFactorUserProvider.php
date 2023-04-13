<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Security\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;

final class TwoFactorUserProvider implements TwoFactorUserProviderInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry
    ) {
    }

    public function getFromUser(User $user): ?TwoFactorUser
    {
        return $this->managerRegistry
            ->getRepository(TwoFactorUser::class)
            ->findOneBy(['user' => $user]);
    }
}
