<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

abstract class FilterUserEvent extends Event
{
    public function __construct(protected readonly User $user)
    {
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
