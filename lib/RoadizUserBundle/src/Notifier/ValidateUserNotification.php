<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Notifier;

use RZ\Roadiz\CoreBundle\Entity\User;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

final class ValidateUserNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private readonly User $user,
        private readonly string $validationLink,
        string $subject = '',
        array $channels = [],
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
        $email = new NotificationEmail();
        $email
            ->htmlTemplate('@RoadizUser/email/users/validate_email.html.twig')
            ->textTemplate('@RoadizUser/email/users/validate_email.txt.twig')
            ->subject($this->getSubject())
            ->action('reset_your_password', $this->validationLink)
            ->context([
                'validationLink' => $this->validationLink,
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
