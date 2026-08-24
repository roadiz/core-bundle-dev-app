<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Notifier;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

final class ResetPasswordNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private readonly User $user,
        private readonly string $resetLink,
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
    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): EmailMessage
    {
        $email = new NotificationEmail();
        $email
            ->htmlTemplate($this->htmlTemplate)
            ->textTemplate($this->textTemplate)
            ->subject($this->getSubject())
            ->action('reset_your_password', $this->resetLink)
            ->context([
                'resetLink' => $this->resetLink,
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
