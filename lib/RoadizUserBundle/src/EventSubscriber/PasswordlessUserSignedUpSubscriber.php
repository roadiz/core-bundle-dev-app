<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\EventSubscriber;

use RZ\Roadiz\UserBundle\Event\PasswordlessUserSignedUp;
use RZ\Roadiz\UserBundle\Manager\UserValidationTokenManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PasswordlessUserSignedUpSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UserValidationTokenManagerInterface $userValidationTokenManager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PasswordlessUserSignedUp::class => 'onUserSignedUp',
        ];
    }

    public function onUserSignedUp(PasswordlessUserSignedUp $event): void
    {
        $user = $event->getUser();
        $this->userValidationTokenManager->createForUser($user, false);
    }
}
