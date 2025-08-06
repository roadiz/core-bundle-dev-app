<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Event\Font;

use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FontEvent extends Event
{
    public function __construct(protected ?Font $font)
    {
    }

    public function getFont(): ?Font
    {
        return $this->font;
    }

    public function setFont(?Font $font): FontEvent
    {
        $this->font = $font;

        return $this;
    }
}
