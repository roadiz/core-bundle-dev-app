<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Security\User;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Message\UserPasswordResetLinkNotifyMessage;
use RZ\Roadiz\CoreBundle\Notifier\ResetPasswordNotification;
use RZ\Roadiz\CoreBundle\Security\LoginLink\LoginLinkSenderInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
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
        private MessageBusInterface $messageBus,
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
                $this->generateResetLink($user, $route),
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
     * Same as sendPasswordResetLink(), but dispatches the actual mail send
     * through Messenger instead of blocking on it: an unauthenticated
     * caller (the public password-reset-request endpoints) must not be
     * able to time the response to tell whether an account exists.
     *
     * Requires $user to already be persisted (a real database id) since
     * the handler re-fetches it by id — do not call from a prePersist
     * listener.
     */
    public function sendPasswordResetLinkAsync(
        User $user,
        object|string $route = 'loginResetPage',
        string $htmlTemplate = '@RoadizCore/email/users/reset_password_email.html.twig',
        string $txtTemplate = '@RoadizCore/email/users/reset_password_email.txt.twig',
    ): void {
        $this->messageBus->dispatch(new UserPasswordResetLinkNotifyMessage(
            $user->getId() ?? throw new \RuntimeException('User id is null.'),
            $this->generateResetLink($user, $route),
            $this->translator->trans(
                'reset.password.request',
                locale: $user->getLocale()
            ),
            $htmlTemplate,
            $txtTemplate,
        ));
    }

    private function generateResetLink(User $user, object|string $route): string
    {
        if (\is_string($route)) {
            return $this->urlGenerator->generate(
                $route,
                [
                    'token' => $user->getConfirmationToken(),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
        }

        return $this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [
                RouteObjectInterface::ROUTE_OBJECT => $route,
                'token' => $user->getConfirmationToken(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
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
