<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\User;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Notifier\ResetPasswordNotification;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\LoginLink\LoginLinkDetails;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class UserViewer
{
    public function __construct(
        private NotifierInterface $notifier,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator,
        private LoggerInterface $logger,
        private LoginLinkSenderInterface $loginLinkSender,
    ) {
    }

    /**
     * Send email to reset user password.
     *
     * @throws TransportExceptionInterface
     */
    public function sendPasswordResetLink(
        User $user,
        object|string $route = 'loginResetPage',
        string $htmlTemplate = '@RoadizCore/email/users/reset_password_email.html.twig',
        string $txtTemplate = '@RoadizCore/email/users/reset_password_email.txt.twig',
    ): bool {
        try {
            $notification = new ResetPasswordNotification(
                $user,
                $this->urlGenerator,
                $route,
                $this->translator->trans(
                    'reset.password.request',
                    locale: $user->getLocale()
                ),
                ['email'],
                $htmlTemplate,
                $txtTemplate
            );
            $this->notifier->send($notification, new Recipient(
                $user->getEmail() ?? throw new \InvalidArgumentException('User has no email address.'),
            ));

            return true;
        } catch (\Exception $e) {
            // Silent error not to prevent user creation if mailer is not configured
            $this->logger->error('Unable to send password reset link', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
                'entity' => $user,
            ]);

            return false;
        }
    }

    /**
     * @deprecated Use LoginLinkSenderInterface::sendLoginLink instead
     */
    public function sendLoginLink(
        UserInterface $user,
        LoginLinkDetails $loginLinkDetails,
        string $htmlTemplate = '@RoadizCore/email/users/login_link_email.html.twig',
        string $txtTemplate = '@RoadizCore/email/users/login_link_email.txt.twig',
    ): void {
        $this->loginLinkSender->sendLoginLink($user, $loginLinkDetails);
    }
}
