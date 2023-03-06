<?php

declare(strict_types=1);

namespace RZ\Roadiz\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\CoreBundle\Entity\User;

#[ORM\Table(name: 'user_metadata')]
#[ORM\Entity]
class UserMetadata
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: true, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(name: 'metadata', type: 'json', nullable: true)]
    private ?array $metadata = [];

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return UserMetadata
     */
    public function setId(int $id): UserMetadata
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
     * @return UserMetadata
     */
    public function setUser(?User $user): UserMetadata
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    /**
     * @param  array|null $metadata
     * @return UserMetadata
     */
    public function setMetadata(?array $metadata): UserMetadata
    {
        $this->metadata = $metadata;
        return $this;
    }
}
