<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Abstract base class for entity thumbnail providers.
 */
abstract class AbstractEntityThumbnailProvider implements EntityThumbnailProviderInterface
{
    /**
     * Create a standardized response array.
     *
     * @param string|null $url The thumbnail URL
     * @param string|null $alt The alt text
     * @param string|null $title The title/tooltip text
     * @return array{url: string|null, alt: string|null, title: string|null}
     */
    protected function createResponse(?string $url, ?string $alt = null, ?string $title = null): array
    {
        return [
            'url' => $url,
            'alt' => $alt,
            'title' => $title,
        ];
    }
}
