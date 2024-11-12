<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Event\Font;

use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FontEvent extends Event
{
    protected ?Font $font = null;

    public function __construct(?Font $font)
    {
        $this->font = $font;
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
