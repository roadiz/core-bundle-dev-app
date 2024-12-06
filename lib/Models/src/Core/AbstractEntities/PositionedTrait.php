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

    public function setPosition(float $newPosition): PositionedInterface
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
