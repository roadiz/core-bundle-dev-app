<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TooManyLoginAttemptsException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Too many login attemps, wait before trying again.';
    }
}
