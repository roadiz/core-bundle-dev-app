<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Registers back-office section roots in BreadcrumbRoots, for sections Rozier does not ship.
 *
 * Implement it in your project or bundle, autoconfiguration tags it, and the sections it returns
 * become available to `breadcrumb_root()` / `breadcrumb_trail()` like the built-in ones. A section
 * already declared elsewhere is overridden by the last provider returning it.
 */
#[AutoconfigureTag('roadiz_rozier.breadcrumb_root_provider')]
interface BreadcrumbRootProviderInterface
{
    /**
     * @return array<string, array{string, string}> section name => [translation key, listing route name]
     */
    public function getRoots(): array;
}
