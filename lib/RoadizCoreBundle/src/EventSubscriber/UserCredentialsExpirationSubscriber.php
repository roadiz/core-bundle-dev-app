<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent;
use RZ\Roadiz\CoreBundle\Security\LogTrail;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserCredentialsExpirationSubscriber implements EventSubscriberInterface
{
    public function __construct(private LogTrail $logTrail, private TranslatorInterface $translator)
    {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            UserPasswordChangedEvent::class => 'onUserPasswordChanged',
        ];
    }

    public function onUserPasswordChanged(UserPasswordChangedEvent $event): void
    {
        $user = $event->getUser();
        /*
         * If user was forced to update its credentials,
         * we remove expiration.
         */
        if (!$user->isCredentialsNonExpired()) {
            if (null !== $user->getCredentialsExpiresAt()) {
                $user->setPasswordRequestedAt(null);
                $user->setConfirmationToken(null);
                $user->setCredentialsExpiresAt(null);
            }
        }

        $this->logTrail->publishConfirmMessage(
            null,
            $this->translator->trans('user.changed_password'),
            $user,
        );
    }
}
