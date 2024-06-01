<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Handlers;

use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;

interface HandlerFactoryInterface
{
    public function getHandler(AbstractEntity $entity): AbstractHandler;
}
