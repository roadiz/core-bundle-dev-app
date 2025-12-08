<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\UserBundle\Repository\UserValidationTokenRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Table(name: 'user_validation_tokens'),
    UniqueEntity('token'),
    ORM\Entity(repositoryClass: UserValidationTokenRepository::class)
]
class UserValidationToken
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: UserInterface::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'CASCADE')]
    private ?UserInterface $user = null;

    #[ORM\Column(name: 'token', type: 'string', length: 255, unique: true, nullable: false)]
    #[Assert\Length(max: 255)]
    private string $token;

    #[ORM\Column(name: 'token_valid_until', type: 'datetime', unique: false, nullable: true)]
    private ?\DateTime $tokenValidUntil = null;

    public function getId(): int
    {
        return $this->id ?? throw new \LogicException('Id is not set yet.');
    }

    public function setId(int $id): UserValidationToken
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(?UserInterface $user): UserValidationToken
    {
        $this->user = $user;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function setToken(string $token): UserValidationToken
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenValidUntil(): ?\DateTime
    {
        return $this->tokenValidUntil;
    }

    public function setTokenValidUntil(?\DateTime $tokenValidUntil): UserValidationToken
    {
        $this->tokenValidUntil = $tokenValidUntil;

        return $this;
    }
}
