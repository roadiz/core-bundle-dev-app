<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

/**
 * Trait which describe a positioned entity.
 */
trait PositionedTrait
{
    public function getPosition(): float
    {
        return $this->position;
    }

    /**
     * Set position as a float to enable increment and decrement by O.5
     * to insert a node between two others.
     *
     * @return $this
     */
    public function setPosition(float $newPosition)
    {
        if ($newPosition > -1) {
            $this->position = $newPosition;
        }

        return $this;
    }

    public function compareTo($other): int
    {
        if ($other instanceof PositionedInterface) {
            return $this->getPosition() <=> $other->getPosition();
        }
        throw new \LogicException('Cannot compare object which does not implement '.PositionedInterface::class);
    }
}
