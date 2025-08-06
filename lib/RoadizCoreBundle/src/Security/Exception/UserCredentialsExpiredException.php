<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class UserCredentialsExpiredException extends AuthenticationException
{
    #[\Override]
    public function getMessageKey(): string
    {
        return 'Your credentials have expired. Please request a new password.';
    }
}
