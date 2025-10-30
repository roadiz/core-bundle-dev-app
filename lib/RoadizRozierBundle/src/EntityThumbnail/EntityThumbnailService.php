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
     * Get thumbnail information for any entity by class name and identifier.
     *
     * @param class-string $entityClass The entity class name
     * @param int|string $identifier The entity identifier
     * @return array{url: string|null, alt: string|null, title: string|null}|null
     */
    public function getThumbnail(string $entityClass, int|string $identifier): ?array
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($entityClass, $identifier)) {
                return $provider->getThumbnail($entityClass, $identifier);
            }
        }

        return null;
    }
}
