<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements BreadcrumbsItemFactoryInterface<NodesSources>
 */
#[AutoconfigureTag('roadiz_rozier.breadcrumbs_item_factory', ['priority' => 0])]
final readonly class NodesSourcesBreadcrumbsItemFactory implements BreadcrumbsItemFactoryInterface
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
            $item->getTitle() ?? $item->getNode()->getNodeName(),
            $item->getNode()->isHidingChildren() ?
                $this->urlGenerator->generate(
                    'nodesTreePage',
                    [
                        'nodeId' => $item->getNode()->getId(),
                        'translationId' => $item->getTranslation()->getId(),
                    ]
                ) :
                $this->urlGenerator->generate(
                    'nodesEditSourcePage',
                    [
                        'nodeId' => $item->getNode()->getId(),
                        'translationId' => $item->getTranslation()->getId(),
                    ]
                ),
            $item->getNode()->isHome(),
        );
    }

    #[\Override]
    public function support(?object $item): bool
    {
        return $item instanceof NodesSources;
    }
}
