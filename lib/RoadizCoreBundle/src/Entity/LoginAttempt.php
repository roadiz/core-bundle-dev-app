<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Repository\LoginAttemptRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: LoginAttemptRepository::class),
    ORM\Table(name: "login_attempts"),
    ORM\Index(columns: ["username"]),
    ORM\Index(columns: ["blocks_login_until", "username"]),
    ORM\Index(columns: ["blocks_login_until", "username", "ip_address"])
]
class LoginAttempt
{
    #[
        ORM\Id,
        ORM\Column(type: "integer"),
        ORM\GeneratedValue(strategy: "AUTO")
    ]
    private int $id;

    #[ORM\Column(name: 'ip_address', type: 'string', length: 46, nullable: true)]
    #[Assert\Length(max: 46)]
    private ?string $ipAddress = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $date;

    #[ORM\Column(name: 'blocks_login_until', type: 'datetime', nullable: true)]
    private ?\DateTime $blocksLoginUntil = null;

    #[ORM\Column(name: 'username', type: 'string', length: 255, unique: false, nullable: false)]
    #[Assert\Length(max: 255)]
    private string $username = '';

    #[ORM\Column(name: 'attempt_count', type: 'integer', nullable: true)]
    private ?int $attemptCount = null;

    public function __construct(?string $ipAddress, ?string $username)
    {
        $this->ipAddress = $ipAddress;
        $this->username = $username;
        $this->date = new \DateTimeImmutable('now');
        $this->blocksLoginUntil = new \DateTime('now');
        $this->attemptCount = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @return \DateTime|null
     */
    public function getBlocksLoginUntil(): ?\DateTime
    {
        return $this->blocksLoginUntil;
    }

    /**
     * @param \DateTime $blocksLoginUntil
     *
     * @return LoginAttempt
     */
    public function setBlocksLoginUntil(\DateTime $blocksLoginUntil): LoginAttempt
    {
        $this->blocksLoginUntil = $blocksLoginUntil;

        return $this;
    }

    /**
     * @return int
     */
    public function getAttemptCount(): int
    {
        return $this->attemptCount;
    }

    /**
     * @return LoginAttempt
     */
    public function addAttemptCount(): LoginAttempt
    {
        $this->attemptCount++;
        return $this;
    }
}
