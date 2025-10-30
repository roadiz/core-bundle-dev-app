<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\EntityThumbnail;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * Value object representing entity thumbnail information.
 */
#[Exclude]
final readonly class EntityThumbnail
{
    public function __construct(
        public ?string $url = null,
        public ?string $alt = null,
        public ?string $title = null,
        public ?int $width = null,
        public ?int $height = null,
    ) {
    }
}
