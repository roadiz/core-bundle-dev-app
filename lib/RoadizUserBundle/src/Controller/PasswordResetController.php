<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use RZ\Roadiz\CoreBundle\Entity\User;

final class PasswordResetController
{
    public function __invoke(User $data): User
    {
        return $data;
    }
}
