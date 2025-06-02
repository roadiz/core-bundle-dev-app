<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\User;

use Symfony\Component\Security\Core\User\UserInterface;

final readonly class PreviewUser implements UserInterface
{
    public function __construct(
        private string $username,
        private array $roles = [],
    ) {
    }

    #[\Override]
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): string
    {
        throw new \BadMethodCallException('Preview user does not have a password');
    }

    public function getSalt(): string
    {
        throw new \BadMethodCallException('Preview user does not have a password salt');
    }

    #[\Override]
    public function eraseCredentials(): void
    {
        throw new \BadMethodCallException('Preview user cannot erase its credentials');
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
