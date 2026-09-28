<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Event\User\UserPasswordChangedEvent;
use RZ\Roadiz\CoreBundle\Notifier\ResetPasswordNotification;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Warn users by email whenever their password changes, so that a
 * compromised session or another account cannot silently take over theirs.
 */
final readonly class UserPasswordChangedNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private NotifierInterface $notifier,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
    ) {
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
        if (empty($user->getEmail())) {
            return;
        }

        try {
            $this->notifier->send(new ResetPasswordNotification(
                $user,
                $this->urlGenerator->generate('loginRequestPage', [], UrlGeneratorInterface::ABSOLUTE_URL),
                $this->translator->trans('password.changed.notification', locale: $user->getLocale()),
                ['email'],
                '@RoadizCore/email/users/password_changed_email.html.twig',
                '@RoadizCore/email/users/password_changed_email.txt.twig',
            ), new Recipient($user->getEmail()));
        } catch (\Exception $e) {
            // Silent error not to prevent password update if mailer is not configured
            $this->logger->error('Unable to send password changed notification', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'entity' => $user,
            ]);
        }
    }
}
