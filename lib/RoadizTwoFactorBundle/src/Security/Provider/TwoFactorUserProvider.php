<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Security\Provider;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Entity\TwoFactorUser;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;

final class TwoFactorUserProvider implements TwoFactorUserProviderInterface
{
    public function __construct(
        private readonly ManagerRegistry $managerRegistry,
        private readonly TotpAuthenticatorInterface $totpAuthenticator,
    ) {
    }

    public function getFromUser(User $user): ?TwoFactorUser
    {
        return $this->managerRegistry
            ->getRepository(TwoFactorUser::class)
            ->findOneBy(['user' => $user]);
    }

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

    public function activate(TwoFactorUser $user): void
    {
        $user->setActivatedAt(new \DateTime());
        $this->managerRegistry->getManager()->flush();
    }

    public function disable(TwoFactorUser $user): void
    {
        $this->managerRegistry->getManager()->remove($user);
        $this->managerRegistry->getManager()->flush();
    }

    public function generateBackupCodes(TwoFactorUser $user): array
    {
        $length = $user->getDigits();
        // generate 10 random numeric codes of $length
        $codes = [];
        for ($i = 0; $i < 10; ++$i) {
            // use random_int to generate a random number of $length
            $digits = [];
            for ($j = 0; $j < $length; ++$j) {
                $digits[] = (string) \random_int(0, 9);
            }
            $code = implode('', $digits);

            $user->addBackupCode($code);
            $codes[] = $code;
        }
        $this->managerRegistry->getManager()->flush();

        return $codes;
    }
}
