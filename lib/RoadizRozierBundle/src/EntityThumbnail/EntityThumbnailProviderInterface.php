<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Chain of Responsibility pattern interface for providing entity thumbnails.
 */
interface EntityThumbnailProviderInterface
{
    /**
     * Check if this provider can handle the given entity.
     *
     * @param object $entity The entity to check
     * @return bool True if this provider can handle the entity
     */
    public function supports(object $entity): bool;

    /**
     * Get thumbnail information for the given entity.
     *
     * @param object $entity The entity to get thumbnail for
     * @return array{url: string|null, alt: string|null, title: string|null} Thumbnail data
     */
    public function getThumbnail(object $entity): array;
}
