<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\User;

use Symfony\Component\Security\Core\User\UserInterface;

class PreviewUser implements UserInterface
{
    protected string $username;
    protected array $roles = [];

    public function __construct(string $username, array $roles)
    {
        $this->username = $username;
        $this->roles = $roles;
    }

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

    public function eraseCredentials(): void
    {
        throw new \BadMethodCallException('Preview user cannot erase its credentials');
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
