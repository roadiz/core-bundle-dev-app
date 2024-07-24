<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * An AbstractEntity with datetime fields to keep track of time with your items.
 */

#[
    ORM\MappedSuperclass,
    ORM\HasLifecycleCallbacks,
    ORM\Table,
    ORM\Index(columns: ["created_at"]),
    ORM\Index(columns: ["updated_at"]),
]
abstract class AbstractDateTimed extends AbstractEntity
{
    /**
     * @var DateTime|null
     */
    #[
        ORM\Column(name: "created_at", type: "datetime", nullable: true),
        Serializer\Groups(["timestamps"]),
        SymfonySerializer\Groups(["timestamps"]),
    ]
    protected ?DateTime $createdAt = null;

    /**
     * @var DateTime|null
     */
    #[
        ORM\Column(name: "updated_at", type: "datetime", nullable: true),
        Serializer\Groups(["timestamps"]),
        SymfonySerializer\Groups(["timestamps"]),
    ]
    protected ?DateTime $updatedAt = null;

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime|null $createdAt
     * @return $this
     */
    public function setCreatedAt(?DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTime|null $updatedAt
     * @return $this
     */
    public function setUpdatedAt(?DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    protected function initAbstractDateTimed(): void
    {
        $this->setUpdatedAt(new DateTime("now"));
        $this->setCreatedAt(new DateTime("now"));
    }

    /**
     * @return void
     */
    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->setUpdatedAt(new DateTime("now"));
    }
    /**
     * @return void
     */
    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->setUpdatedAt(new DateTime("now"));
        $this->setCreatedAt(new DateTime("now"));
    }
    /**
     * Set creation and update date to *now*.
     *
     * @return AbstractEntity
     */
    public function resetDates()
    {
        $this->setCreatedAt(new DateTime("now"));
        $this->setUpdatedAt(new DateTime("now"));

        return $this;
    }
}
