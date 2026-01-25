<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

/**
 * @template TEntity of object
 */
interface BreadcrumbsItemFactoryInterface
{
    /**
     * @param TEntity|null $item
     */
    public function createBreadcrumbsItem(?object $item): ?BreadcrumbsItem;

    public function support(?object $item): bool;
}
