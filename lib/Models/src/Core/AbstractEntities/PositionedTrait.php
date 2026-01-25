<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Trait which describe a positioned entity.
 */
trait PositionedTrait
{
    #[ORM\Column(type: 'float'),
        Serializer\Groups(['position']),
        ApiFilter(RangeFilter::class),
        ApiFilter(NumericFilter::class)]
    protected float $position = 0.0;

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
