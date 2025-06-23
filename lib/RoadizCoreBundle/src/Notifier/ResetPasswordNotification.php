<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Notifier;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ResetPasswordNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private readonly User $user,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly object|string $resetLinkRoute,
        string $subject = '',
        array $channels = [],
        private readonly string $htmlTemplate = '@RoadizCore/email/users/reset_password_email.html.twig',
        private readonly string $textTemplate = '@RoadizCore/email/users/reset_password_email.txt.twig',
    ) {
        parent::__construct($subject, $channels);
    }

    #[\Override]
    public function getChannels(RecipientInterface $recipient): array
    {
        return ['email'];
    }

    #[\Override]
    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): ?EmailMessage
    {
        if (is_string($this->resetLinkRoute)) {
            $resetLink = $this->urlGenerator->generate(
                $this->resetLinkRoute,
                [
                    'token' => $this->user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        } else {
            $resetLink = $this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $this->resetLinkRoute,
                    'token' => $this->user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        $email = new NotificationEmail();
        $email
            ->htmlTemplate($this->htmlTemplate)
            ->textTemplate($this->textTemplate)
            ->subject($this->getSubject())
            ->action('reset_your_password', $resetLink)
            ->context([
                'resetLink' => $resetLink,
                'user' => $this->user,
            ])
            ->markAsPublic()
        ;

        if (null !== $this->user->getLocale()) {
            $email->locale($this->user->getLocale());
        }

        return new EmailMessage($email);
    }
}
