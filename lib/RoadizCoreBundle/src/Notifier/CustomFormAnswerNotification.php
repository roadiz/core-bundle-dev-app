<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Notifier;

use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Notification\EmailNotificationInterface;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

final class CustomFormAnswerNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private readonly array $context,
        private readonly string $locale = 'en',
        /**
         * @var array<DataPart>
         */
        private readonly array $resources = [],
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
            ->htmlTemplate('@RoadizCore/email/forms/answerForm.html.twig')
            ->textTemplate('@RoadizCore/email/forms/answerForm.txt.twig')
        ;

        foreach ($this->resources as $resource) {
            if (!$resource instanceof DataPart) {
                continue;
            }
            $email->addPart($resource);
        }

        if (null !== $this->replyTo) {
            $email->replyTo($this->replyTo);
        }

        return new EmailMessage($email);
    }
}
