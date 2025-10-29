<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Service to manage entity thumbnail providers using Chain of Responsibility pattern.
 */
final class EntityThumbnailService
{
    /**
     * @param iterable<EntityThumbnailProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $providers,
    ) {
    }

    /**
     * Get thumbnail information for any entity.
     *
     * @param object $entity The entity to get thumbnail for
     * @return array{url: string|null, alt: string|null, title: string|null}|null
     */
    public function getThumbnail(object $entity): ?array
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($entity)) {
                return $provider->getThumbnail($entity);
            }
        }

        return null;
    }
}
