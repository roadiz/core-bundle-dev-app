<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Tests\Security\Provider;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\AuthenticatorTwoFactorProvider;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\TwoFactorFormRendererInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Regression guard: beginAuthentication() returning false means 2FA is
 * SKIPPED entirely for that authentication attempt. Every "false" branch
 * below must be a deliberate skip, never an accidental one.
 */
final class AuthenticatorTwoFactorProviderTest extends TestCase
{
    private function createProvider(TwoFactorUserProviderInterface $twoFactorUserProvider): AuthenticatorTwoFactorProvider
    {
        return new AuthenticatorTwoFactorProvider(
            $twoFactorUserProvider,
            $this->createMock(TotpAuthenticatorInterface::class),
            $this->createMock(TwoFactorFormRendererInterface::class),
        );
    }

    private function createContext(UserInterface $user): AuthenticationContextInterface
    {
        $context = $this->createMock(AuthenticationContextInterface::class);
        $context->method('getUser')->willReturn($user);

        return $context;
    }

    public function testBeginAuthenticationReturnsFalseForNonAppUser(): void
    {
        $twoFactorUserProvider = $this->createMock(TwoFactorUserProviderInterface::class);
        $twoFactorUserProvider->expects(self::never())->method('getFromUser');

        $provider = $this->createProvider($twoFactorUserProvider);
        $context = $this->createContext($this->createMock(UserInterface::class));

        self::assertFalse($provider->beginAuthentication($context));
    }

    public function testBeginAuthenticationReturnsFalseWhenUserHasNoTwoFactorRecord(): void
    {
        $user = new User();
        $twoFactorUserProvider = $this->createMock(TwoFactorUserProviderInterface::class);
        $twoFactorUserProvider->method('getFromUser')->with($user)->willReturn(null);

        $provider = $this->createProvider($twoFactorUserProvider);

        self::assertFalse($provider->beginAuthentication($this->createContext($user)));
    }

    public function testBeginAuthenticationReturnsFalseWhenTotpIsNotEnabled(): void
    {
        $user = new User();
        // Default TwoFactorUser: no secret, no activatedAt => isTotpAuthenticationEnabled() is false.
        $twoFactorUser = new TwoFactorUser();

        $twoFactorUserProvider = $this->createMock(TwoFactorUserProviderInterface::class);
        $twoFactorUserProvider->method('getFromUser')->with($user)->willReturn($twoFactorUser);

        $provider = $this->createProvider($twoFactorUserProvider);

        self::assertFalse($provider->beginAuthentication($this->createContext($user)));
    }

    public function testBeginAuthenticationReturnsTrueWhenTotpIsEnabled(): void
    {
        $user = new User();
        $twoFactorUser = (new TwoFactorUser())
            ->setSecret('JBSWY3DPEHPK3PXP')
            ->setActivatedAt(new \DateTime());

        $twoFactorUserProvider = $this->createMock(TwoFactorUserProviderInterface::class);
        $twoFactorUserProvider->method('getFromUser')->with($user)->willReturn($twoFactorUser);

        $provider = $this->createProvider($twoFactorUserProvider);

        self::assertTrue($provider->beginAuthentication($this->createContext($user)));
    }
}
