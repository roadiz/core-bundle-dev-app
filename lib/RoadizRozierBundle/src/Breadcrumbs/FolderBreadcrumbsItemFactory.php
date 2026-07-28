<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements BreadcrumbsItemFactoryInterface<Folder>
 */
#[AutoconfigureTag('roadiz_rozier.breadcrumbs_item_factory', ['priority' => 0])]
final readonly class FolderBreadcrumbsItemFactory implements BreadcrumbsItemFactoryInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    public function createBreadcrumbsItem(?object $item): ?BreadcrumbsItem
    {
        if (null === $item) {
            return null;
        }

        return new BreadcrumbsItem(
            $item->getName() ?? $item->getFolderName(),
            $this->urlGenerator->generate(
                'foldersEditPage',
                [
                    'folderId' => $item->getId(),
                ]
            ),
            false,
        );
    }

    #[\Override]
    public function support(?object $item): bool
    {
        return $item instanceof Folder;
    }
}
