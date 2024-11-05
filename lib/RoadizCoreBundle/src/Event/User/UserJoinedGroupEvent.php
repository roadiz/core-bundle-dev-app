<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\User;

use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\FilterUserEvent;

final class UserJoinedGroupEvent extends FilterUserEvent
{
    public function __construct(User $user, private readonly Group $group)
    {
        parent::__construct($user);
    }

    public function getGroup(): Group
    {
        return $this->group;
    }
}
