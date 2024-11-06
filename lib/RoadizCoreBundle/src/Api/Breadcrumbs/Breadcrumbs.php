<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Breadcrumbs implements BreadcrumbsInterface
{
    /**
     * @param PersistableInterface[] $items
     */
    public function __construct(
        #[Serializer\Groups(['breadcrumbs', 'web_response'])]
        #[Serializer\MaxDepth(1)]
        private readonly array $items,
    ) {
    }

    /**
     * @return PersistableInterface[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
