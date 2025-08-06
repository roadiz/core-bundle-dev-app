<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\LoginLink;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Notifier\LoginLinkNotification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailLoginLinkSender implements LoginLinkSenderInterface
{
    public function __construct(
        private NotifierInterface $notifier,
        private TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function sendLoginLink(UserInterface $user, LoginLinkDetails $loginLinkDetails): void
    {
        if ($user instanceof User && !$user->isEnabled()) {
            throw new \InvalidArgumentException('User must be enabled to send a login link.');
        }

        if (!\method_exists($user, 'getEmail')) {
            throw new \InvalidArgumentException('User implementation must have getEmail method.');
        }

        if (null === $user->getEmail()) {
            throw new \InvalidArgumentException('User must have an email to send a login link.');
        }

        if ($user instanceof User && null !== $user->getLocale()) {
            $subject = $this->translator->trans(
                'login_link.request',
                locale: $user->getLocale(),
            );
        } else {
            $subject = $this->translator->trans(
                'login_link.request',
            );
        }

        $notification = new LoginLinkNotification(
            $user,
            $loginLinkDetails,
            $subject,
            ['email']
        );
        $this->notifier->send($notification, new Recipient($user->getEmail()));
    }
}
