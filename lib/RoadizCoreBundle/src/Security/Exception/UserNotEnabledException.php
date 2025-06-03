<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class UserNotEnabledException extends AuthenticationException
{
    #[\Override]
    public function getMessageKey(): string
    {
        return 'Your user account is not enabled. Contact an administrator.';
    }
}
