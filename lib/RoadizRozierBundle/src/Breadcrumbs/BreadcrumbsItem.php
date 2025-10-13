<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class BreadcrumbsItem
{
    public function __construct(
        public string $label,
        public ?string $url,
        public bool $home = false,
        public bool $enabled = true,
    ) {
    }
}
