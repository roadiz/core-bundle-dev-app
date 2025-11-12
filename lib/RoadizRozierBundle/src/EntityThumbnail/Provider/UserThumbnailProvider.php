<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Repository\UserRepository;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;

/**
 * Provides thumbnail for User entities using Gravatar.
 * Supports both integer IDs and email addresses as identifiers.
 */
final readonly class UserThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    #[\Override]
    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, User::class);
    }

    #[\Override]
    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail
    {
        if (!$this->isClassSupported($entityClass, User::class)) {
            return null;
        }

        // If identifier is numeric, try by ID first
        if (is_numeric($identifier)) {
            $user = $this->userRepository->find((int) $identifier);
        } else {
            // Otherwise try by email/username
            $user = $this->userRepository->findOneByUserIdentifier($identifier);
        }

        if (!$user instanceof User) {
            return null;
        }

        $username = $user->getUserIdentifier();

        return new EntityThumbnail(
            url: $user->getPictureUrl(),
            alt: $username,
            title: $user->getPublicName() ?? $username,
        );
    }
}
