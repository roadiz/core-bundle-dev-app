<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\LoginLink;

use RZ\Roadiz\CoreBundle\Bag\Settings;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Mailer\EmailManagerFactory;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class EmailLoginLinkSender implements LoginLinkSenderInterface
{
    public function __construct(
        private Settings $settingsBag,
        private EmailManagerFactory $emailManagerFactory,
        private TranslatorInterface $translator,
        private string $htmlTemplate = '@RoadizCore/email/users/login_link_email.html.twig',
        private string $txtTemplate = '@RoadizCore/email/users/login_link_email.txt.twig',
    ) {
    }

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

        $emailManager = $this->emailManagerFactory->create();
        $emailContact = $this->settingsBag->get('email_sender', null);
        if (!\is_string($emailContact)) {
            throw new \InvalidArgumentException('Email sender must be a string.');
        }
        $siteName = $this->settingsBag->get('site_name', null);
        if (!\is_string($siteName)) {
            throw new \InvalidArgumentException('Site name must be a string.');
        }

        $emailManager->setAssignation([
            'loginLink' => $loginLinkDetails->getUrl(),
            'expiresAt' => $loginLinkDetails->getExpiresAt(),
            'user' => $user,
            'site' => $siteName,
            'mailContact' => $emailContact,
        ]);
        $emailManager->setEmailTemplate($this->htmlTemplate);
        $emailManager->setEmailPlainTextTemplate($this->txtTemplate);
        $emailManager->setSubject($this->translator->trans(
            'login_link.request'
        ));

        $emailManager->setReceiver($user->getEmail());
        $emailManager->setSender([$emailContact => $siteName]);

        // Send the message
        $emailManager->send();
    }
}
