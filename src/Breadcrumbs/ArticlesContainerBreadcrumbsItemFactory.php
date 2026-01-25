<?php

declare(strict_types=1);

namespace App\Breadcrumbs;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbsItem;
use RZ\Roadiz\RozierBundle\Breadcrumbs\BreadcrumbsItemFactoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Example of overriding node breadcrumbs item for shadow container.
 *
 * @implements BreadcrumbsItemFactoryInterface<NodesSources>
 */
#[AutoconfigureTag('roadiz_rozier.breadcrumbs_item_factory', ['priority' => 10])]
final readonly class ArticlesContainerBreadcrumbsItemFactory implements BreadcrumbsItemFactoryInterface
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

        if ($item instanceof Node) {
            $item = $item->getNodeSources()->first() ?: throw new \RuntimeException('Node has no source');
        }

        return new BreadcrumbsItem(
            $item->getTitle() ?? $item->getNode()->getNodeName(),
            $this->urlGenerator->generate(
                'appArticlesListPage'
            ),
            $item->getNode()->isHome(),
        );
    }

    #[\Override]
    public function support(?object $item): bool
    {
        return ($item instanceof NodesSources && 'articles' === $item->getNode()->getNodeName())
               || ($item instanceof Node && 'articles' === $item->getNodeName());
    }
}
