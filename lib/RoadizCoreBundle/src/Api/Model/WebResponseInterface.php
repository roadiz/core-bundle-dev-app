<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsInterface;

/**
 * @template T of PersistableInterface
 */
interface WebResponseInterface
{
    public function setHead(?NodesSourcesHeadInterface $head): self;
    public function setBreadcrumbs(?BreadcrumbsInterface $breadcrumbs): self;

    /**
     * @param T|null $item
     * @return self
     */
    public function setItem(?PersistableInterface $item): self;
    public function setPath(?string $path): self;
    /**
     * @return T|null
     */
    public function getItem(): ?PersistableInterface;
    public function getMaxAge(): ?int;
    public function setMaxAge(?int $maxAge): self;
}
