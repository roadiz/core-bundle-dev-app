<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;

/**
 * Provides thumbnail for User entities using Gravatar.
 * Supports both integer IDs and email addresses as identifiers.
 */
final class UserThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
    ) {
    }

    public function supports(string $entityClass, int|string $identifier): bool
    {
        return $this->isClassSupported($entityClass, User::class);
    }

    public function getThumbnail(string $entityClass, int|string $identifier): ?array
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

        $email = $user->getEmail();
        $username = $user->getUsername();
        $title = $username;

        if (null === $email) {
            return $this->createResponse(null, $username, $title);
        }

        // Generate Gravatar URL
        $hash = md5(strtolower(trim($email)));
        $gravatarUrl = sprintf(
            'https://www.gravatar.com/avatar/%s?s=64&d=mp&r=g',
            $hash
        );

        return $this->createResponse($gravatarUrl, $username, $title);
    }
}
