<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract entity for any Human-like objects.
 *
 * This class can be extended for *Users*, *Subscribers*, etc.
 */
#[ORM\MappedSuperclass,
    ORM\Table,
    ORM\HasLifecycleCallbacks]
abstract class AbstractHuman implements DateTimedInterface, PersistableInterface
{
    use SequentialIdTrait;
    use DateTimedTrait;

    #[ORM\Column(type: 'string', length: 200, unique: true, nullable: false)]
    #[Serializer\Groups(['user_personal', 'human'])]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 200)]
    #[Assert\Email]
    #[ApiFilter(OrderFilter::class)]
    #[ApiFilter(SearchFilter::class)]
    // @phpstan-ignore-next-line
    protected ?string $email = null;

    /**
     * Public name (pseudonyme) that can be displayed to a public audience.
     */
    #[ORM\Column(name: 'publicName', type: 'string', length: 250, nullable: true),
        Serializer\Groups(['user_public', 'human']),
        Assert\Length(max: 250)]
    protected ?string $publicName = null;

    #[ORM\Column(name: 'firstName', type: 'string', length: 250, nullable: true),
        Serializer\Groups(['user_personal', 'human']),
        Assert\Length(max: 250)]
    protected ?string $firstName = null;

    #[ORM\Column(name: 'lastName', type: 'string', length: 250, nullable: true),
        Serializer\Groups(['user_personal', 'human']),
        Assert\Length(max: 250)]
    protected ?string $lastName = null;

    #[ORM\Column(type: 'string', length: 250, nullable: true),
        Serializer\Groups(['user_personal', 'human']),
        Assert\Length(max: 250)]
    protected ?string $company = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return $this
     */
    public function setEmail(?string $email): AbstractHuman
    {
        if (false !== filter_var($email ?? '', FILTER_VALIDATE_EMAIL)) {
            $this->email = $email;
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return $this
     */
    public function setFirstName(?string $firstName): AbstractHuman
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return $this
     */
    public function setLastName(?string $lastName): AbstractHuman
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @return $this
     */
    public function setCompany(?string $company): AbstractHuman
    {
        $this->company = $company;

        return $this;
    }

    public function getPublicName(): ?string
    {
        return $this->publicName;
    }

    public function setPublicName(?string $publicName): void
    {
        $this->publicName = $publicName;
    }
}
