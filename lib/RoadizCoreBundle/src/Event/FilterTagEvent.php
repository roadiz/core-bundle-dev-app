<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterTagEvent extends Event
{
    public function __construct(protected readonly Tag $tag)
    {
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
