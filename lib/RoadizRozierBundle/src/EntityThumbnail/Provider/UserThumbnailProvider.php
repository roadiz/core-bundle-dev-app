<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;
use RZ\Roadiz\RozierBundle\EntityThumbnail\EntityThumbnail;

/**
 * Provides thumbnail for User entities using Gravatar.
 * Supports both integer IDs and email addresses as identifiers.
 */
final readonly class UserThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
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

        // Try to fetch user by ID or email
        $repository = $this->managerRegistry->getRepository($entityClass);

        // If identifier is numeric, try by ID first
        if (is_numeric($identifier)) {
            $user = $repository->find((int) $identifier);
        } else {
            // Otherwise try by email
            $user = $repository->findOneBy(['email' => $identifier]);
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
