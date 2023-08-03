<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class UserLockedException extends AuthenticationException
{
    public function getMessageKey(): string
    {
        return 'Your user account is locked. Contact an administrator.';
    }
}
