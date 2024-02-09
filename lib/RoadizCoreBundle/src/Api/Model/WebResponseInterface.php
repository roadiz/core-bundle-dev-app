<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Api\Breadcrumbs\BreadcrumbsInterface;

interface WebResponseInterface
{
    public function setHead(?NodesSourcesHeadInterface $head): self;
    public function setBreadcrumbs(?BreadcrumbsInterface $breadcrumbs): self;
    public function setItem(?PersistableInterface $item): self;
    public function setPath(?string $path): self;
    public function getItem(): ?PersistableInterface;
}
