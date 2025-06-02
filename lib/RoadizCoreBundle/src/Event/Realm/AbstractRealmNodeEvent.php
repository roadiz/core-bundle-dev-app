<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Realm;

use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use Symfony\Contracts\EventDispatcher\Event;

abstract class AbstractRealmNodeEvent extends Event
{
    public function __construct(private readonly RealmNode $realmNode)
    {
    }

    public function getRealmNode(): RealmNode
    {
        return $this->realmNode;
    }
}
