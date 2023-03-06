<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\EventSubscriber;

use RZ\Roadiz\UserBundle\Event\UserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UserSignedUpSubscriber implements EventSubscriberInterface
{
    private UserValidationTokenManagerInterface $userValidationTokenManager;

    public function __construct(
        UserValidationTokenManagerInterface $userValidationTokenManager
    ) {
        $this->userValidationTokenManager = $userValidationTokenManager;
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserSignedUp::class => 'onUserSignedUp'
        ];
    }

    public function onUserSignedUp(UserSignedUp $event): void
    {
        $user = $event->getUser();
        $this->userValidationTokenManager->createForUser($user);
    }
}
