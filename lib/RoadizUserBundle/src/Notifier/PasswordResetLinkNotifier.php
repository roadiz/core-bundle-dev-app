<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Notifier;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\CoreBundle\Message\UserPasswordResetLinkNotifyMessage;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Builds a password-reset link (route name or hard-coded frontend URL) and
 * dispatches it asynchronously via the shared UserPasswordResetLinkNotifyMessage.
 * Shared between UserPasswordRequestProcessor (password_request) and
 * UserSignupProcessor (out-of-band notice on a signup collision).
 */
final readonly class PasswordResetLinkNotifier
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private UrlGeneratorInterface $urlGenerator,
        private string $passwordResetUrl,
    ) {
    }

    public function notify(User $user, string $linkLocale, string $subject, string $htmlTemplate, string $textTemplate): void
    {
        $this->messageBus->dispatch(new UserPasswordResetLinkNotifyMessage(
            $user->getId() ?? throw new \RuntimeException('User id is null.'),
            $this->generateResetLink($user, $linkLocale),
            $subject,
            $htmlTemplate,
            $textTemplate,
        ));
    }

    private function generateResetLink(User $user, string $linkLocale): string
    {
        $parameters = [
            'token' => $user->getConfirmationToken(),
            '_locale' => $linkLocale,
        ];

        try {
            return $this->urlGenerator->generate($this->passwordResetUrl, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException) {
            return $this->passwordResetUrl.'?'.http_build_query($parameters);
        }
    }
}
