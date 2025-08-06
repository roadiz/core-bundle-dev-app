<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Notifier;

use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

final class BaseEmailNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(private readonly array $context, string $subject = '', array $channels = [])
    {
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
            ->htmlTemplate('@RoadizCore/email/base_email.html.twig')
            ->textTemplate('@RoadizCore/email/base_email.txt.twig')
            ->subject($this->getSubject())
            ->context($this->context)
            ->markAsPublic()
        ;

        return new EmailMessage($email);
    }
}
