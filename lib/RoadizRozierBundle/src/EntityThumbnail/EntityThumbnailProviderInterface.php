<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Chain of Responsibility pattern interface for providing entity thumbnails.
 */
interface EntityThumbnailProviderInterface
{
    /**
     * Check if this provider can handle the given entity class and identifier.
     *
     * @param class-string $entityClass The entity class name
     * @param int|string   $identifier  The entity identifier
     *
     * @return bool True if this provider can handle the entity
     */
    public function supports(string $entityClass, int|string $identifier): bool;

    /**
     * Get thumbnail information for the given entity class and identifier.
     * Provider is responsible for fetching the entity.
     *
     * @param class-string $entityClass The entity class name
     * @param int|string   $identifier  The entity identifier
     *
     * @return EntityThumbnail|null Thumbnail data or null if entity not found
     */
    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail;
}
