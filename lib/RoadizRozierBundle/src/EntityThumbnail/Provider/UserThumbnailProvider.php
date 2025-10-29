<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail\Provider;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\RozierBundle\EntityThumbnail\AbstractEntityThumbnailProvider;

/**
 * Provides thumbnail for User entities using Gravatar.
 */
final class UserThumbnailProvider extends AbstractEntityThumbnailProvider
{
    public function supports(object $entity): bool
    {
        return $entity instanceof User;
    }

    public function getThumbnail(object $entity): array
    {
        if (!$entity instanceof User) {
            return $this->createResponse(null);
        }

        $email = $entity->getEmail();
        $username = $entity->getUsername();
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
