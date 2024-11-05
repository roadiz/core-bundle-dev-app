<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

interface PositionedInterface
{
    public function getPosition(): float;

    /**
     * Set position as a float to enable increment and decrement by O.5
     * to insert an entity between two others.
     *
     * @return PositionedInterface
     */
    public function setPosition(float $newPosition);
}
