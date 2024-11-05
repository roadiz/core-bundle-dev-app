<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Repository\TwoFactorUserRepository;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: TwoFactorUserRepository::class),
    ORM\Table(name: 'two_factor_users'),
    ORM\UniqueConstraint(columns: ['user_id']),
]
class TwoFactorUser implements TotpTwoFactorInterface, BackupCodeInterface, TrustedDeviceInterface, GoogleAuthenticatorTwoFactorInterface
{
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\Id]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    // @phpstan-ignore-next-line
    private ?User $user = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $secret = null;

    /*
     * Prevents TOTP code request if user never activated first time: if never setup-ed its TOTP app.
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $activatedAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $backupCodes = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $trustedVersion = 1;

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    #[Assert\Length(max: 6)]
    private ?string $algorithm = TotpConfiguration::ALGORITHM_SHA1;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $period = 30;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $digits = 6;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): TwoFactorUser
    {
        $this->user = $user;

        return $this;
    }

    public function setSecret(?string $secret): TwoFactorUser
    {
        $this->secret = $secret;

        return $this;
    }

    public function getActivatedAt(): ?\DateTimeInterface
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(?\DateTimeInterface $activatedAt): TwoFactorUser
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    public function getAlgorithm(): string
    {
        return $this->algorithm ?? TotpConfiguration::ALGORITHM_SHA1;
    }

    public function setAlgorithm(?string $algorithm): TwoFactorUser
    {
        $this->algorithm = $algorithm;

        return $this;
    }

    public function getPeriod(): int
    {
        return $this->period ?? 30;
    }

    public function setPeriod(?int $period): TwoFactorUser
    {
        $this->period = $period;

        return $this;
    }

    public function getDigits(): int
    {
        return $this->digits ?? 6;
    }

    public function setDigits(?int $digits): TwoFactorUser
    {
        $this->digits = $digits;

        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return (bool) $this->secret && null !== $this->activatedAt;
    }

    public function getTotpAuthenticationUsername(): string
    {
        if (null === $this->user) {
            throw new \RuntimeException('User cannot be null');
        }

        return $this->user->getUserIdentifier();
    }

    public function getTotpAuthenticationConfiguration(): ?TotpConfigurationInterface
    {
        // You could persist the other configuration options in the user entity to make it individual per user.
        return new TotpConfiguration($this->secret, $this->getAlgorithm(), $this->getPeriod(), $this->getDigits());
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return (bool) $this->secret
            && null !== $this->activatedAt
            && 6 === $this->digits
            && 30 === $this->period
            && TotpConfiguration::ALGORITHM_SHA1 === $this->algorithm
        ;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        if (null === $this->user) {
            throw new \RuntimeException('User cannot be null');
        }

        return $this->user->getUserIdentifier();
    }

    public function getGoogleAuthenticatorSecret(): ?string
    {
        return $this->secret;
    }

    /**
     * Check if it is a valid backup code.
     */
    public function isBackupCode(string $code): bool
    {
        // Loop over all backup codes and check if the code is valid
        foreach ($this->backupCodes as $backupCode) {
            if (password_verify($code, $backupCode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Invalidate a backup code.
     */
    public function invalidateBackupCode(string $code): void
    {
        // Loop over all backup codes and check if the code is valid to invalidate it
        foreach ($this->backupCodes as $key => $backupCode) {
            if (password_verify($code, $backupCode)) {
                unset($this->backupCodes[$key]);
                $this->backupCodes = array_values($this->backupCodes);
            }
        }
    }

    /**
     * Add a backup code.
     */
    public function addBackUpCode(string $backUpCode): void
    {
        if (!$this->isBackupCode($backUpCode)) {
            $this->backupCodes[] = password_hash($backUpCode, PASSWORD_BCRYPT);
        }
    }

    public function getTrustedTokenVersion(): int
    {
        return $this->trustedVersion;
    }
}
