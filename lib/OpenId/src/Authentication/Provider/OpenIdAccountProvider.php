<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Authentication\Provider;

use RZ\Roadiz\OpenId\User\OpenIdAccount;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OpenIdAccountProvider implements UserProviderInterface
{
    /**
     * @deprecated since Symfony 5.3, use loadUserByIdentifier() instead
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        throw new UserNotFoundException('Cannot load an OpenId account with its email.');
    }

    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new UserNotFoundException('Cannot load an OpenId account with its email.');
    }

    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof OpenIdAccount) {
            if ($user->getJwtToken()->isExpired(new \DateTime('now'))) {
                throw new UserNotFoundException('OpenId token has expired, please authenticate again…');
            }

            return $user;
        }

        throw new UnsupportedUserException();
    }

    /**
     * @param class-string $class
     */
    #[\Override]
    public function supportsClass(string $class): bool
    {
        return OpenIdAccount::class === $class;
    }
}
