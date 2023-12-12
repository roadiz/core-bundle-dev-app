<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Security\Provider;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Exception\TwoFactorProviderLogicException;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorProviderInterface;

final class AuthenticatorTwoFactorProvider implements TwoFactorProviderInterface
{
    public function __construct(
        private TwoFactorUserProviderInterface $twoFactorUserProvider,
        private TotpAuthenticatorInterface $authenticator,
        private TwoFactorFormRendererInterface $formRenderer,
    ) {
    }

    public function beginAuthentication(AuthenticationContextInterface $context): bool
    {
        $user = $context->getUser();
        if (!($user instanceof User)) {
            return false;
        }

        $twoFactorUser = $this->getTwoFactorFromUser($user);

        if (!($twoFactorUser instanceof TwoFactorInterface && $twoFactorUser->isTotpAuthenticationEnabled())) {
            return false;
        }

        $totpConfiguration = $twoFactorUser->getTotpAuthenticationConfiguration();
        if (null === $totpConfiguration) {
            throw new TwoFactorProviderLogicException(
                'User has to provide a TotpAuthenticationConfiguration for TOTP authentication.'
            );
        }

        $secret = $totpConfiguration->getSecret();
        if (0 === \mb_strlen($secret)) {
            throw new TwoFactorProviderLogicException(
                'User has to provide a secret code for TOTP authentication.'
            );
        }

        return true;
    }

    public function prepareAuthentication(object $user): void
    {
    }

    public function validateAuthenticationCode(object $user, string $authenticationCode): bool
    {
        if ($user instanceof User) {
            $user = $this->getTwoFactorFromUser($user);
        }

        if (!($user instanceof TwoFactorInterface)) {
            return false;
        }

        return $this->authenticator->checkCode($user, $authenticationCode);
    }

    public function getFormRenderer(): TwoFactorFormRendererInterface
    {
        return $this->formRenderer;
    }

    private function getTwoFactorFromUser(User $user): ?TwoFactorUser
    {
        return $this->twoFactorUserProvider->getFromUser($user);
    }
}
