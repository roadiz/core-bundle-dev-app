<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\User;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @package RZ\Roadiz\CoreBundle\EntityHandler
 */
class UserProvider implements UserProviderInterface
{
    protected ManagerRegistry $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $username
     * @return UserInterface
     * @deprecated since Symfony 5.3, use loadUserByIdentifier() instead
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByUsernameOrEmail($username);
    }

    protected function loadUserByUsernameOrEmail(string $identifier): UserInterface
    {
        /** @var User|null $user */
        $user = $this->managerRegistry
            ->getRepository(User::class)
            ->findOneBy(['username' => $identifier]);

        if ($user === null) {
            /** @var User|null $user */
            $user = $this->managerRegistry
                ->getRepository(User::class)
                ->findOneBy(['email' => $identifier]);
        }

        if ($user !== null) {
            return $user;
        } else {
            throw new UserNotFoundException();
        }
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return $this->loadUserByUsernameOrEmail($identifier);
    }

    /**
     * Refreshes the user for the account interface.
     *
     * It is up to the implementation to decide if the user data should be
     * totally reloaded (e.g. from the database), or if the RZ\Roadiz\CoreBundle\Entity\User
     * object can just be merged into some internal array of users / identity
     * map.
     *
     * @param UserInterface $user
     * @return User
     * @throws UnsupportedUserException
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if ($user instanceof User) {
            $manager = $this->managerRegistry->getManagerForClass(User::class);
            /** @var User|null $refreshUser */
            $refreshUser = $manager->find(User::class, (int) $user->getId());
            if (
                $refreshUser !== null &&
                $refreshUser->isEnabled() &&
                $refreshUser->isAccountNonExpired() &&
                $refreshUser->isAccountNonLocked()
            ) {
                // Always refresh User from database: too much related entities to rely only on token.
                return $refreshUser;
            } else {
                throw new UserNotFoundException('Token user does not exist anymore, authenticate again…');
            }
        }
        throw new UnsupportedUserException();
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param class-string $class
     * @return bool
     */
    public function supportsClass($class): bool
    {
        return $class === User::class;
    }
}
