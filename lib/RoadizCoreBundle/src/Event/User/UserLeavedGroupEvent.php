<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\User;

use RZ\Roadiz\CoreBundle\Entity\Group;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\FilterUserEvent;

final class UserLeavedGroupEvent extends FilterUserEvent
{
    private Group $group;

    public function __construct(User $user, Group $group)
    {
        parent::__construct($user);
        $this->group = $group;
    }

    /**
     * @return Group
     */
    public function getGroup(): Group
    {
        return $this->group;
    }
}
