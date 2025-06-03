<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class UserExpiredException extends AuthenticationException
{
    #[\Override]
    public function getMessageKey(): string
    {
        return 'Your account has expired. Contact an administrator.';
    }
}
