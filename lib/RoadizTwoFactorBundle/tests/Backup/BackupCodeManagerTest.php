<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Tests\Backup;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Backup\BackupCodeManager;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;

final class BackupCodeManagerTest extends TestCase
{
    private function createManager(TwoFactorUserProviderInterface $provider, PersisterInterface $persister): BackupCodeManager
    {
        return new BackupCodeManager($provider, $persister);
    }

    /**
     * Reads the private, bcrypt-hashed backup codes array directly, since
     * TwoFactorUser exposes no public getter for it.
     *
     * @return array<int, mixed>
     */
    private function getRawBackupCodes(TwoFactorUser $twoFactorUser): array
    {
        $property = new \ReflectionProperty(TwoFactorUser::class, 'backupCodes');
        $property->setAccessible(true);

        return $property->getValue($twoFactorUser) ?? [];
    }

    public function testValidBackupCodeVerifiesAndInvalidationRemovesUsability(): void
    {
        $twoFactorUser = new TwoFactorUser();
        $twoFactorUser->addBackUpCode('code-one');
        $twoFactorUser->addBackUpCode('code-two');
        $twoFactorUser->addBackUpCode('code-three');

        $user = new User();
        $provider = $this->createMock(TwoFactorUserProviderInterface::class);
        $provider->method('getFromUser')->with($user)->willReturn($twoFactorUser);

        $persister = $this->createMock(PersisterInterface::class);
        $persister->expects(self::once())->method('persist')->with($twoFactorUser);

        $manager = $this->createManager($provider, $persister);

        // Real bcrypt hash comparison via password_verify(), no mocking.
        self::assertTrue($manager->isBackupCode($user, 'code-two'));

        $manager->invalidateBackupCode($user, 'code-two');

        // Second use of the same (now invalidated) code fails.
        self::assertFalse($manager->isBackupCode($user, 'code-two'));

        // Untouched codes remain valid.
        self::assertTrue($manager->isBackupCode($user, 'code-one'));
        self::assertTrue($manager->isBackupCode($user, 'code-three'));

        // Remaining codes are re-indexed sequentially (array_values), no gap left behind.
        self::assertSame([0, 1], array_keys($this->getRawBackupCodes($twoFactorUser)));
    }

    public function testIsBackupCodeReturnsFalseWhenUserHasNoTwoFactorRecord(): void
    {
        $user = new User();
        $provider = $this->createMock(TwoFactorUserProviderInterface::class);
        $provider->method('getFromUser')->with($user)->willReturn(null);

        $manager = $this->createManager($provider, $this->createMock(PersisterInterface::class));

        self::assertFalse($manager->isBackupCode($user, 'anything'));
    }

    public function testInvalidateBackupCodeDoesNothingAndNeverPersistsWhenUserIsNotBackupCodeCapable(): void
    {
        $provider = $this->createMock(TwoFactorUserProviderInterface::class);
        $persister = $this->createMock(PersisterInterface::class);
        $persister->expects(self::never())->method('persist');

        $manager = $this->createManager($provider, $persister);

        $manager->invalidateBackupCode(new \stdClass(), 'anything');

        $this->addToAssertionCount(1);
    }
}
