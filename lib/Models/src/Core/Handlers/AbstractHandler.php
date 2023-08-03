<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Handlers;

use Doctrine\Persistence\ObjectManager;

abstract class AbstractHandler
{
    protected ObjectManager $objectManager;

    /**
     * @return ObjectManager
     */
    public function getObjectManager(): ObjectManager
    {
        return $this->objectManager;
    }

    /**
     * @param ObjectManager $objectManager
     * @return static
     */
    public function setObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
        return $this;
    }

    /**
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Clean positions for current entity siblings.
     *
     * @param bool $setPositions
     * @return float Return the next position after the **last** entity
     */
    public function cleanPositions(bool $setPositions = true): float
    {
        return 1;
    }
}
