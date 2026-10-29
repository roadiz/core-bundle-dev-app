<?php

declare(strict_types=1);

namespace App\Breadcrumbs;

use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbRootProviderInterface;

/**
 * Declares the "articles" back-office section root, so article templates can call
 * `breadcrumb_root('articles')` like any Rozier section.
 */
final class ArticleBreadcrumbRoots implements BreadcrumbRootProviderInterface
{
    #[\Override]
    public function getRoots(): array
    {
        return [
            'articles' => ['articles', 'appArticlesListPage'],
        ];
    }
}
