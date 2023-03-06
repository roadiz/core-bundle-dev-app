<?php

declare(strict_types=1);

namespace RZ\Roadiz\FontBundle\Event\Font;

use RZ\Roadiz\FontBundle\Entity\Font;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FontEvent extends Event
{
    /**
     * @var Font|null
     */
    protected ?Font $font = null;

    /**
     * @param Font|null $font
     */
    public function __construct(?Font $font)
    {
        $this->font = $font;
    }

    /**
     * @return Font|null
     */
    public function getFont(): ?Font
    {
        return $this->font;
    }

    /**
     * @param Font|null $font
     * @return FontEvent
     */
    public function setFont(?Font $font): FontEvent
    {
        $this->font = $font;
        return $this;
    }
}
