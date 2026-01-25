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
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;

final class LoginLinkNotification extends Notification implements EmailNotificationInterface
{
    public function __construct(
        private readonly UserInterface $user,
        private readonly LoginLinkDetails $loginLinkDetails,
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
    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): EmailMessage
    {
        $email = new NotificationEmail();
        $email
            ->htmlTemplate('@RoadizCore/email/users/login_link_email.html.twig')
            ->textTemplate('@RoadizCore/email/users/login_link_email.txt.twig')
            ->subject($this->getSubject())
            ->action('login', $this->loginLinkDetails->getUrl())
            ->context([
                'loginLink' => $this->loginLinkDetails->getUrl(),
                'expiresAt' => $this->loginLinkDetails->getExpiresAt(),
                'user' => $this->user,
            ])
            ->markAsPublic()
        ;

        if ($this->user instanceof User && null !== $this->user->getLocale()) {
            $email->locale($this->user->getLocale());
        }

        return new EmailMessage($email);
    }
}
