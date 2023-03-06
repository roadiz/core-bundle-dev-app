<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Entity\User;
use RZ\Roadiz\UserBundle\Repository\UserValidationTokenRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'token', type: 'string', unique: true, nullable: false)]
    private string $token;

    #[ORM\Column(name: 'token_valid_until', type: 'datetime', unique: false, nullable: true)]
    private ?\DateTime $tokenValidUntil = null;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserValidationToken
     */
    public function setId(int $id): UserValidationToken
    {
        $this->id = $id;
        return $this;
    }


    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param  User|null $user
     * @return UserValidationToken
     */
    public function setUser(?User $user): UserValidationToken
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param  string $token
     * @return UserValidationToken
     */
    public function setToken(string $token): UserValidationToken
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getTokenValidUntil(): ?\DateTime
    {
        return $this->tokenValidUntil;
    }

    /**
     * @param  \DateTime|null $tokenValidUntil
     * @return UserValidationToken
     */
    public function setTokenValidUntil(?\DateTime $tokenValidUntil): UserValidationToken
    {
        $this->tokenValidUntil = $tokenValidUntil;
        return $this;
    }
}
