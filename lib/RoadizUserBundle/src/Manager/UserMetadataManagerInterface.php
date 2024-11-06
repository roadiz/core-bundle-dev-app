<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Manager;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Entity\UserMetadata;

interface UserMetadataManagerInterface
{
    public function getMetadataForUser(User $user): ?UserMetadata;

    public function createMetadataForUser(User $user): UserMetadata;
}
