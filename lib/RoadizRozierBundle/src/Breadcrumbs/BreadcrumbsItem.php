<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class BreadcrumbsItem
{
    /**
     * @param bool $home    deprecated since 2.8, ignored, removed in 2.9
     * @param bool $enabled deprecated since 2.8, ignored, removed in 2.9
     */
    public function __construct(
        public string $label,
        public ?string $url,
        /** @deprecated since 2.8, ignored by the breadcrumb trail, removed in 2.9 */
        public bool $home = false,
        /** @deprecated since 2.8, ignored by the breadcrumb trail, removed in 2.9 */
        public bool $enabled = true,
    ) {
        if (\func_num_args() > 2) {
            trigger_deprecation('roadiz/rozier-bundle', '2.8', 'Passing "$home" or "$enabled" to "%s" is deprecated and ignored; pass only "$label" and "$url". Both arguments will be removed in 2.9.', self::class);
        }
    }
}
