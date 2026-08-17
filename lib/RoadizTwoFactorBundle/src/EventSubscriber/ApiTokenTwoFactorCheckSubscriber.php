<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\EventSubscriber;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Backup\BackupCodeManager;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\JsonLoginAuthenticator;
use Symfony\Component\Security\Http\Event\CheckPassportEvent;

/**
 * Requires a valid TOTP or backup code as an extra credential when a
 * 2FA-enabled account authenticates via POST /api/token: Scheb 2FA is
 * session-based and cannot run on the stateless api_login firewall, so
 * without this, a stolen password alone mints a full-access JWT.
 * Non-2FA accounts are unaffected.
 */
final readonly class ApiTokenTwoFactorCheckSubscriber implements EventSubscriberInterface
{
    private const string CODE_FIELD = '_auth_code';

    public function __construct(
        private RequestStack $requestStack,
        private TwoFactorUserProviderInterface $twoFactorUserProvider,
        private TotpAuthenticatorInterface $totpAuthenticator,
        private BackupCodeManager $backupCodeManager,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        // Must run after Symfony's CheckCredentialsListener (priority 1024)
        // so a wrong password fails before a 2FA code is even requested.
        return [
            CheckPassportEvent::class => ['onCheckPassport', -10],
        ];
    }

    public function onCheckPassport(CheckPassportEvent $event): void
    {
        if (!$event->getAuthenticator() instanceof JsonLoginAuthenticator) {
            return;
        }

        $user = $event->getPassport()->getUser();
        if (!$user instanceof User) {
            return;
        }

        $twoFactorUser = $this->twoFactorUserProvider->getFromUser($user);
        if (!$twoFactorUser instanceof TwoFactorUser || !$twoFactorUser->isTotpAuthenticationEnabled()) {
            return;
        }

        $code = $this->getSubmittedCode();
        if (null === $code) {
            throw new CustomUserMessageAuthenticationException('Two-factor authentication code required.');
        }

        $isValidTotp = $this->totpAuthenticator->checkCode($twoFactorUser, $code);
        if (!$isValidTotp && !$this->backupCodeManager->isBackupCode($user, $code)) {
            throw new CustomUserMessageAuthenticationException('Two-factor authentication code required.');
        }

        if (!$isValidTotp) {
            $this->backupCodeManager->invalidateBackupCode($user, $code);
        }
    }

    private function getSubmittedCode(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return null;
        }

        try {
            $data = json_decode($request->getContent(), true, flags: \JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        $code = \is_array($data) ? ($data[self::CODE_FIELD] ?? null) : null;

        return \is_string($code) && '' !== $code ? $code : null;
    }
}
