<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

/**
 * Value object representing entity thumbnail information.
 */
final readonly class EntityThumbnail
{
    public function __construct(
        public ?string $url = null,
        public ?string $alt = null,
        public ?string $title = null,
    ) {
    }
}
