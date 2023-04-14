<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Security\Provider;

use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;

interface TwoFactorUserProviderInterface
{
    public function getFromUser(User $user): ?TwoFactorUser;

    public function createForUser(User $user): TwoFactorUser;

    public function activate(TwoFactorUser $user): void;

    public function disable(TwoFactorUser $user): void;

    public function generateBackupCodes(TwoFactorUser $user): array;
}
