<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\UrlAlias;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterUrlAliasEvent extends Event
{
    public function __construct(protected readonly UrlAlias $urlAlias)
    {
    }

    public function getUrlAlias(): UrlAlias
    {
        return $this->urlAlias;
    }
}
