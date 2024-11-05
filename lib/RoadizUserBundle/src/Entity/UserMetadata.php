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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): UserMetadata
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): UserMetadata
    {
        $this->user = $user;

        return $this;
    }

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): UserMetadata
    {
        $this->metadata = $metadata;

        return $this;
    }
}
