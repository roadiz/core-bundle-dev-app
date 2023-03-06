<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Breadcrumbs;

interface BreadcrumbsInterface
{
    public function getItems(): array;
}
