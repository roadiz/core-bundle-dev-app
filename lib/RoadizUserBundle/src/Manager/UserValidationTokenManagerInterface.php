<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Manager;

use RZ\Roadiz\UserBundle\Entity\UserValidationToken;
use Symfony\Component\Security\Core\User\UserInterface;

interface UserValidationTokenManagerInterface
{
    public function createForUser(UserInterface $user): UserValidationToken;
    public function isUserEmailValidated(UserInterface $user): bool;
}
