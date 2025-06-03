<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Serializer\Attribute as Serializer;

final readonly class Breadcrumbs implements BreadcrumbsInterface
{
    /**
     * @param PersistableInterface[] $items
     */
    public function __construct(
        #[Serializer\Groups(['breadcrumbs', 'web_response'])]
        #[Serializer\MaxDepth(1)]
        private array $items,
    ) {
    }

    /**
     * @return PersistableInterface[]
     */
    #[\Override]
    public function getItems(): array
    {
        return array_values($this->items);
    }
}
