<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Breadcrumbs implements BreadcrumbsInterface
{
    /**
     * @var array<PersistableInterface>
     */
    #[Serializer\Groups(["breadcrumbs", "web_response"])]
    #[Serializer\MaxDepth(1)]
    private array $items;

    /**
     * @param array<PersistableInterface> $items
     */
    public function __construct(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return PersistableInterface[]
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
