<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Backup;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Security\Provider\TwoFactorUserProviderInterface;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\PersisterInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Backup\BackupCodeManagerInterface;

final class BackupCodeManager implements BackupCodeManagerInterface
{
    public function __construct(
        private readonly TwoFactorUserProviderInterface $twoFactorUserProvider,
        private readonly PersisterInterface $persister,
    ) {
    }

    public function isBackupCode(object $user, string $code): bool
    {
        if ($user instanceof User) {
            $user = $this->twoFactorUserProvider->getFromUser($user);
        }

        if ($user instanceof BackupCodeInterface) {
            return $user->isBackupCode($code);
        }

        return false;
    }

    public function invalidateBackupCode(object $user, string $code): void
    {
        if ($user instanceof User) {
            $user = $this->twoFactorUserProvider->getFromUser($user);
        }

        if (!($user instanceof BackupCodeInterface)) {
            return;
        }

        $user->invalidateBackupCode($code);
        $this->persister->persist($user);
    }
}
