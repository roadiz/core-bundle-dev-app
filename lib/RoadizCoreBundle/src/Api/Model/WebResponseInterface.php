<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Model;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;

interface WebResponseInterface
{
    public function getItem(): ?PersistableInterface;
}
