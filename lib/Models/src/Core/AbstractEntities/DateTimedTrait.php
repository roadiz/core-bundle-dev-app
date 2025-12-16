<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;

trait DateTimedTrait
{
    #[
        ORM\Column(name: 'created_at', type: 'datetime', nullable: true),
        Serializer\Groups(['timestamps']),
    ]
    protected ?\DateTime $createdAt = null;

    #[
        ORM\Column(name: 'updated_at', type: 'datetime', nullable: true),
        Serializer\Groups(['timestamps']),
    ]
    protected ?\DateTime $updatedAt = null;

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return $this
     */
    public function setCreatedAt(?\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return $this
     */
    public function setUpdatedAt(?\DateTime $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    protected function initDateTimedTrait(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        $this->setCreatedAt(new \DateTime('now'));
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $this->setUpdatedAt(new \DateTime('now'));
        $this->setCreatedAt(new \DateTime('now'));
    }

    /**
     * Set creation and update date to *now*.
     *
     * @return $this
     */
    public function resetDates(): static
    {
        $this->setCreatedAt(new \DateTime('now'));
        $this->setUpdatedAt(new \DateTime('now'));

        return $this;
    }
}
