<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A group gather User and Roles.
 */
#[
    ORM\Entity(repositoryClass: GroupRepository::class),
    ORM\Table(name: 'usergroups'),
    UniqueEntity(fields: ['name'])
]
class Group implements PersistableInterface, \Stringable
{
    use SequentialIdTrait;

    #[ORM\Column(type: 'string', length: 250, unique: true)]
    #[Serializer\Groups(['user', 'role', 'role:export', 'role:import', 'group', 'group:export', 'group:import'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    #[Serializer\Groups(['group_user'])]
    private Collection $users;

    /**
     * @var array<string> roles assigned to this Group
     */
    #[ORM\Column(name: 'group_roles', type: 'json', nullable: true)]
    #[Serializer\Groups(['group', 'user', 'group:export'])]
    private ?array $roles = [];

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function getRoles(): array
    {
        return $this->roles ?? [];
    }

    public function setRoles(array $roles): Group
    {
        $this->roles = array_values(array_unique(array_filter($roles)));

        return $this;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getName();
    }
}
