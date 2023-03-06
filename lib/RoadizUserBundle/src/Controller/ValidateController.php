<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Controller;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Event\UserEmailValidated;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ValidateController
{
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(User $data): User
    {
        $this->eventDispatcher->dispatch(new UserEmailValidated($data));
        return $data;
    }
}
