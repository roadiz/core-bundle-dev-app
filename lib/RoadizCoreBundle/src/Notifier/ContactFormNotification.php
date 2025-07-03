<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Notifier;

use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Address;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

final class ContactFormNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private readonly array $context,
        private readonly string $locale = 'en',
        /**
         * @var array<string, UploadedFile>
         */
        private readonly array $files = [],
        private readonly ?Address $replyTo = null,
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
            ->context($this->context)
            ->to($recipient->getEmail())
            ->subject($this->getSubject())
            ->locale($this->locale)
            ->markAsPublic()
            ->htmlTemplate('@RoadizCore/email/forms/contactForm.html.twig')
            ->textTemplate('@RoadizCore/email/forms/contactForm.txt.twig')
        ;

        foreach ($this->files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }
            $email->attachFromPath($file->getRealPath(), $file->getClientOriginalName());
        }

        if (null !== $this->replyTo) {
            $email->replyTo($this->replyTo);
        }

        return new EmailMessage($email);
    }
}
