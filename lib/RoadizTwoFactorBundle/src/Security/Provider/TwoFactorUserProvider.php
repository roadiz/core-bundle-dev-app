<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Security\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

final readonly class TwoFactorUserProvider implements TwoFactorUserProviderInterface
{
    /**
     * Alphanumeric charset excluding visually ambiguous characters (0/O, 1/I/L),
     * matching the convention already used in RZ\Roadiz\Random\PasswordGenerator.
     */
    private const string BACKUP_CODE_ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    /**
     * 10 chars over a 31-char alphabet ≈ 49.5 bits of entropy, independent of the
     * TOTP `digits` setting (was ~20 bits for a 6-digit numeric code).
     */
    private const int BACKUP_CODE_LENGTH = 10;

    public function __construct(
        private ManagerRegistry $managerRegistry,
        private TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    #[\Override]
    public function getFromUser(User $user): ?TwoFactorUser
    {
        return $this->managerRegistry
            ->getRepository(TwoFactorUser::class)
            ->findOneBy(['user' => $user]);
    }

    #[\Override]
    public function createForUser(User $user): TwoFactorUser
    {
        $twoFactorUser = $this->getFromUser($user);
        if ($twoFactorUser instanceof TwoFactorUser) {
            return $twoFactorUser;
        }

        $twoFactorUser = new TwoFactorUser();
        $twoFactorUser->setUser($user);
        $twoFactorUser->setSecret($this->totpAuthenticator->generateSecret());
        $this->managerRegistry->getManager()->persist($twoFactorUser);
        $this->managerRegistry->getManager()->flush();

        return $twoFactorUser;
    }

    #[\Override]
    public function activate(TwoFactorUser $user): void
    {
        $user->setActivatedAt(new \DateTime());
        $this->managerRegistry->getManager()->flush();
    }

    #[\Override]
    public function disable(TwoFactorUser $user): void
    {
        $this->managerRegistry->getManager()->remove($user);
        $this->managerRegistry->getManager()->flush();
    }

    #[\Override]
    public function generateBackupCodes(TwoFactorUser $user): array
    {
        // generate 10 random alphanumeric codes, independent of the TOTP digit count
        $codes = [];
        for ($i = 0; $i < 10; ++$i) {
            $code = $this->generateBackupCode();
            $user->addBackupCode($code);
            $codes[] = $code;
        }
        $this->managerRegistry->getManager()->flush();

        return $codes;
    }

    private function generateBackupCode(): string
    {
        $alphabetLength = \strlen(self::BACKUP_CODE_ALPHABET);
        $code = '';
        for ($i = 0; $i < self::BACKUP_CODE_LENGTH; ++$i) {
            $code .= self::BACKUP_CODE_ALPHABET[\random_int(0, $alphabetLength - 1)];
        }

        return $code;
    }
}
