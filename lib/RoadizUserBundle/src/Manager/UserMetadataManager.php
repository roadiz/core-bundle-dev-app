<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Entity\UserMetadata;

readonly class UserMetadataManager implements UserMetadataManagerInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function getMetadataForUser(User $user): ?UserMetadata
    {
        return $this->managerRegistry->getRepository(UserMetadata::class)->findOneByUser($user);
    }

    public function createMetadataForUser(User $user): UserMetadata
    {
        $userMetadata = new UserMetadata();
        $userMetadata->setUser($user);
        $this->managerRegistry->getManager()->persist($userMetadata);

        return $userMetadata;
    }
}
