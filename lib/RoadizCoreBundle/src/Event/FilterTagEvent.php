<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * @package RZ\Roadiz\CoreBundle\Event
 */
abstract class FilterTagEvent extends Event
{
    protected Tag $tag;

    /**
     * @param Tag $tag
     */
    public function __construct(Tag $tag)
    {
        $this->tag = $tag;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }
}
