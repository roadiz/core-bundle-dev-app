<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\User;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Event\FilterUserEvent;

final class UserUsernameChangedEvent extends FilterUserEvent
{
    public function __construct(User $user, private readonly string $oldUsername, private readonly string $newUsername)
    {
        parent::__construct($user);
    }

    public function getOldUsername(): string
    {
        return $this->oldUsername;
    }

    public function getNewUsername(): string
    {
        return $this->newUsername;
    }
}
