<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Tests\Security\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProvider;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

final class TwoFactorUserProviderTest extends TestCase
{
    private function createProvider(): TwoFactorUserProvider
    {
        $objectManager = $this->createMock(ObjectManager::class);

        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManager')->willReturn($objectManager);

        return new TwoFactorUserProvider(
            $managerRegistry,
            $this->createMock(TotpAuthenticatorInterface::class),
        );
    }

    public function testGenerateBackupCodesReturnsTenUniqueAlphanumericCodesOfExpectedLength(): void
    {
        $twoFactorUser = new TwoFactorUser();
        // Default TOTP digits (6) must no longer bound the backup code length.
        self::assertSame(6, $twoFactorUser->getDigits());

        $codes = $this->createProvider()->generateBackupCodes($twoFactorUser);

        self::assertCount(10, $codes);
        self::assertCount(10, array_unique($codes), 'Generated backup codes must be unique.');

        foreach ($codes as $code) {
            self::assertSame(10, \strlen($code), 'Backup code length must be independent of the TOTP digit count.');
            self::assertMatchesRegularExpression('/^[A-Z0-9]+$/', $code);
            self::assertDoesNotMatchRegularExpression('/^[0-9]+$/', $code, 'Backup codes must not be purely numeric.');
            // Visually ambiguous characters (0/O, 1/I/L) must be excluded.
            self::assertDoesNotMatchRegularExpression('/[01IOL]/', $code);
            self::assertTrue($twoFactorUser->isBackupCode($code));
        }
    }

    public function testGenerateBackupCodesLengthIsIndependentOfTotpDigits(): void
    {
        $twoFactorUser = new TwoFactorUser();
        $twoFactorUser->setDigits(8);

        $codes = $this->createProvider()->generateBackupCodes($twoFactorUser);

        foreach ($codes as $code) {
            self::assertSame(10, \strlen($code));
        }
    }
}
