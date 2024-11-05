<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\Role;

use RZ\Roadiz\CoreBundle\Entity\Role;
use Symfony\Contracts\EventDispatcher\Event;

abstract class RoleEvent extends Event
{
    /**
     * @var Role|null
     */
    protected $role;

    public function __construct(?Role $role)
    {
        $this->role = $role;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): RoleEvent
    {
        $this->role = $role;

        return $this;
    }
}
