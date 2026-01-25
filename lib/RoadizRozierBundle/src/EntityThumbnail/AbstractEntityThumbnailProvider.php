<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Abstract base class for entity thumbnail providers.
 */
abstract readonly class AbstractEntityThumbnailProvider implements EntityThumbnailProviderInterface
{
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
