<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Service to manage entity thumbnail providers using Chain of Responsibility pattern.
 */
final readonly class EntityThumbnailService implements EntityThumbnailProviderInterface
{
    /**
     * @param iterable<EntityThumbnailProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator(tag: 'roadiz.entity_thumbnail_provider', excludeSelf: true)]
        private iterable $providers,
    ) {
    }

    #[\Override]
    public function supports(string $entityClass, int|string $identifier): bool
    {
        return true;
    }

    /**
     * Get thumbnail information for any entity by class name and identifier.
     *
     * @param class-string $entityClass The entity class name
     * @param int|string   $identifier  The entity identifier
     */
    #[\Override]
    public function getThumbnail(string $entityClass, int|string $identifier): ?EntityThumbnail
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($entityClass, $identifier)) {
                return $provider->getThumbnail($entityClass, $identifier);
            }
        }

        return null;
    }
}
