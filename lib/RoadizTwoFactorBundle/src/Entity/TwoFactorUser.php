<?php

declare(strict_types=1);

namespace RZ\Roadiz\TwoFactorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\TwoFactorBundle\Repository\TwoFactorUserRepository;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfiguration;
use Scheb\TwoFactorBundle\Model\Totp\TotpConfigurationInterface;
use Scheb\TwoFactorBundle\Model\Totp\TwoFactorInterface as TotpTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Model\TrustedDeviceInterface;

#[
    ORM\Entity(repositoryClass: TwoFactorUserRepository::class),
    ORM\Table(name: "two_factor_users"),
    ORM\UniqueConstraint(columns: ["user_id"]),
]
class TwoFactorUser implements TotpTwoFactorInterface, BackupCodeInterface, TrustedDeviceInterface, GoogleAuthenticatorTwoFactorInterface
{
    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\Id]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $secret = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $backupCodes = [];

    #[ORM\Column(type: 'integer', nullable: false, options: ['default' => 1])]
    private int $trustedVersion = 1;

    #[ORM\Column(type: 'string', length: 6, nullable: true)]
    private ?string $algorithm = TotpConfiguration::ALGORITHM_SHA1;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $period = 30;

    #[ORM\Column(type: 'smallint', nullable: true)]
    private ?int $digits = 6;

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User|null $user
     * @return TwoFactorUser
     */
    public function setUser(?User $user): TwoFactorUser
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @param string|null $secret
     * @return TwoFactorUser
     */
    public function setSecret(?string $secret): TwoFactorUser
    {
        $this->secret = $secret;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm ?? TotpConfiguration::ALGORITHM_SHA1;
    }

    /**
     * @param string|null $algorithm
     * @return TwoFactorUser
     */
    public function setAlgorithm(?string $algorithm): TwoFactorUser
    {
        $this->algorithm = $algorithm;
        return $this;
    }

    /**
     * @return int
     */
    public function getPeriod(): int
    {
        return $this->period ?? 30;
    }

    /**
     * @param int|null $period
     * @return TwoFactorUser
     */
    public function setPeriod(?int $period): TwoFactorUser
    {
        $this->period = $period;
        return $this;
    }

    /**
     * @return int
     */
    public function getDigits(): int
    {
        return $this->digits ?? 6;
    }

    /**
     * @param int|null $digits
     * @return TwoFactorUser
     */
    public function setDigits(?int $digits): TwoFactorUser
    {
        $this->digits = $digits;
        return $this;
    }

    public function isTotpAuthenticationEnabled(): bool
    {
        return (bool) $this->secret;
    }

    public function getTotpAuthenticationUsername(): string
    {
        if ($this->user === null) {
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
        return (bool) $this->secret;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        if ($this->user === null) {
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
        return in_array($code, $this->backupCodes);
    }

    /**
     * Invalidate a backup code
     */
    public function invalidateBackupCode(string $code): void
    {
        $key = array_search($code, $this->backupCodes);
        if ($key !== false) {
            unset($this->backupCodes[$key]);
        }
    }

    /**
     * Add a backup code
     */
    public function addBackUpCode(string $backUpCode): void
    {
        if (!in_array($backUpCode, $this->backupCodes)) {
            $this->backupCodes[] = $backUpCode;
        }
    }

    public function getTrustedTokenVersion(): int
    {
        return $this->trustedVersion;
    }
}
