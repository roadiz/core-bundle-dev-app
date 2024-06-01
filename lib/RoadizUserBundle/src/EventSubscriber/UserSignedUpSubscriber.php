<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\EventSubscriber;

use RZ\Roadiz\UserBundle\Event\UserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class UserSignedUpSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserValidationTokenManagerInterface $userValidationTokenManager
    ) {
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
