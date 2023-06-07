<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract entity for any Human-like objects.
 *
 * This class can be extended for *Users*, *Subscribers*, etc.
 */
#[
    ORM\MappedSuperclass,
    ORM\Table,
    ORM\HasLifecycleCallbacks
]
abstract class AbstractHuman extends AbstractDateTimed
{
    #[
        ORM\Column(type: "string", length: 200, unique: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"]),
        Assert\NotNull(),
        Assert\NotBlank(),
        Assert\Length(max: 200),
        Assert\Email()
    ]
    protected ?string $email = null;

    /**
     * Public name (pseudonyme) that can be displayed to a public audience.
     */
    #[
        ORM\Column(name: "publicName", type: "string", length: 250, nullable: true),
        Serializer\Groups(["user_public", "human"]),
        SymfonySerializer\Groups(["user_public", "human"]),
        Assert\Length(max: 250)
    ]
    protected ?string $publicName = null;

    #[
        ORM\Column(name: "firstName", type: "string", length: 250, nullable: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"]),
        Assert\Length(max: 250)
    ]
    protected ?string $firstName = null;

    #[
        ORM\Column(name: "lastName", type: "string", length: 250, nullable: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"]),
        Assert\Length(max: 250)
    ]
    protected ?string $lastName = null;

    #[
        ORM\Column(type: "string", length: 50, nullable: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"]),
        Assert\Length(max: 50)
    ]
    protected ?string $phone = null;

    #[
        ORM\Column(type: "string", length: 250, nullable: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"]),
        Assert\Length(max: 250)
    ]
    protected ?string $company = null;

    #[
        ORM\Column(type: "string", length: 250, nullable: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"]),
        Assert\Length(max: 250)
    ]
    protected ?string $job = null;

    #[
        ORM\Column(type: "datetime", nullable: true),
        Serializer\Groups(["user_personal", "human"]),
        SymfonySerializer\Groups(["user_personal", "human"])
    ]
    protected ?DateTime $birthday = null;

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     *
     * @return $this
     */
    public function setEmail(?string $email)
    {
        if (filter_var($email ?? '', FILTER_VALIDATE_EMAIL) !== false) {
            $this->email = $email;
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @param string|null $firstName
     *
     * @return $this
     */
    public function setFirstName(?string $firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @param string|null $lastName
     *
     * @return $this
     */
    public function setLastName(?string $lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     *
     * @return $this
     */
    public function setCompany(?string $company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getJob(): ?string
    {
        return $this->job;
    }

    /**
     * @param string|null $job
     *
     * @return $this
     */
    public function setJob(?string $job)
    {
        $this->job = $job;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getBirthday(): ?DateTime
    {
        return $this->birthday;
    }
    /**
     * @param DateTime|null $birthday
     *
     * @return $this
     */
    public function setBirthday(?DateTime $birthday = null)
    {
        $this->birthday = $birthday;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     *
     * @return self
     */
    public function setPhone(?string $phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPublicName(): ?string
    {
        return $this->publicName;
    }

    /**
     * @param string|null $publicName
     */
    public function setPublicName(?string $publicName): void
    {
        $this->publicName = $publicName;
    }
}
