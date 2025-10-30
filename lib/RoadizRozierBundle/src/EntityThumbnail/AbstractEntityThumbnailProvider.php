<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Abstract base class for entity thumbnail providers.
 */
abstract class AbstractEntityThumbnailProvider implements EntityThumbnailProviderInterface
{
    /**
     * Create a standardized response.
     *
     * @param string|null $url The thumbnail URL
     * @param string|null $alt The alt text
     * @param string|null $title The title/tooltip text
     * @return EntityThumbnail
     */
    protected function createResponse(?string $url, ?string $alt = null, ?string $title = null): EntityThumbnail
    {
        return new EntityThumbnail(
            url: $url,
            alt: $alt,
            title: $title,
        );
    }

    /**
     * Check if the given class is assignable to the expected class.
     *
     * @param class-string $entityClass
     * @param class-string $expectedClass
     */
    protected function isClassSupported(string $entityClass, string $expectedClass): bool
    {
        return is_a($entityClass, $expectedClass, true);
    }
}
