<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class BreadcrumbsItemFactory implements BreadcrumbsItemFactoryInterface
{
    /**
     * @param iterable<BreadcrumbsItemFactoryInterface> $factories
     */
    public function __construct(
        #[AutowireIterator('roadiz_rozier.breadcrumbs_item_factory')]
        private iterable $factories,
    ) {
    }

    #[\Override]
    public function createBreadcrumbsItem(?object $item): ?BreadcrumbsItem
    {
        foreach ($this->factories as $factory) {
            if ($factory->support($item)) {
                return $factory->createBreadcrumbsItem($item);
            }
        }

        return null;
    }

    #[\Override]
    public function support(?object $item): bool
    {
        return true;
    }
}
