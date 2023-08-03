<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Handlers;

use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

interface HandlerFactoryInterface
{
    /**
     * @param AbstractEntity $entity
     * @return AbstractHandler
     */
    public function getHandler(AbstractEntity $entity): AbstractHandler;
}
