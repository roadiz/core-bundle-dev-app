<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Handlers;

use Doctrine\Persistence\ObjectManager;

abstract class AbstractHandler
{
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    public function __construct(
        protected readonly ObjectManager $objectManager,
    ) {
    }

    /**
     * Clean positions for current entity siblings.
     *
     * @return float Return the next position after the **last** entity
     */
    public function cleanPositions(bool $setPositions = true): float
    {
        return 1;
    }
}
