<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\Handlers;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

interface HandlerFactoryInterface
{
    public function getHandler(PersistableInterface $entity): AbstractHandler;
}
