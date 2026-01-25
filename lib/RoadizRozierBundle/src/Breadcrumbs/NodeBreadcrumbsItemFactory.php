<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Breadcrumbs;

use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements BreadcrumbsItemFactoryInterface<Node>
 */
#[AutoconfigureTag('roadiz_rozier.breadcrumbs_item_factory', ['priority' => 0])]
final readonly class NodeBreadcrumbsItemFactory implements BreadcrumbsItemFactoryInterface
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
            (false !== $item->getNodeSources()->first() && null !== $item->getNodeSources()->first()->getTitle())
                ? ($item->getNodeSources()->first()->getTitle())
                : ($item->getNodeName()),
            $item->isHidingChildren() ?
                $this->urlGenerator->generate(
                    'nodesTreePage',
                    [
                        'nodeId' => $item->getId(),
                    ]
                ) :
                $this->urlGenerator->generate(
                    'nodesEditPage',
                    [
                        'nodeId' => $item->getId(),
                    ]
                ),
            $item->isHome(),
        );
    }

    #[\Override]
    public function support(?object $item): bool
    {
        return $item instanceof Node;
    }
}
