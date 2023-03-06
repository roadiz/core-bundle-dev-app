<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Preview\User;

use Symfony\Component\Security\Core\User\UserInterface;

class PreviewUser implements UserInterface
{
    protected string $username;
    protected array $roles = [];

    /**
     * @param string $username
     * @param array $roles
     */
    public function __construct(string $username, array $roles)
    {
        $this->username = $username;
        $this->roles = $roles;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function getPassword(): string
    {
        throw new \BadMethodCallException('Preview user does not have a password');
    }

    /**
     * @inheritDoc
     */
    public function getSalt(): string
    {
        throw new \BadMethodCallException('Preview user does not have a password salt');
    }

    /**
     * @inheritDoc
     */
    public function eraseCredentials(): void
    {
        throw new \BadMethodCallException('Preview user cannot erase its credentials');
    }

    /**
     * @inheritDoc
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
