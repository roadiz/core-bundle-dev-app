<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class OpenIdAuthenticationException extends AuthenticationException
{
    /**
     * Message key to be used by the translation component.
     *
     * @return string
     */
    public function getMessageKey(): string
    {
        return 'An OpenID authentication exception occurred.';
    }
}
