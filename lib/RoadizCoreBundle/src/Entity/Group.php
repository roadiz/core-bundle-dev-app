<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A group gather User and Roles.
 */
#[
    ORM\Entity(repositoryClass: GroupRepository::class),
    ORM\Table(name: "usergroups"),
    UniqueEntity(fields: ["name"])
]
class Group extends AbstractEntity
{
    #[ORM\Column(type: 'string', unique: true)]
    #[SymfonySerializer\Groups(['user', 'role', 'group'])]
    #[Serializer\Groups(['user', 'role', 'group'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $name = '';

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'groups')]
    #[SymfonySerializer\Groups(['group_user'])]
    #[Serializer\Groups(['group_user'])]
    #[Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\User>")]
    private Collection $users;

    /**
     * @var Collection<int, Role>
     */
    #[ORM\JoinTable(name: 'groups_roles')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'groups', cascade: ['persist', 'merge'])]
    #[SymfonySerializer\Groups(['group'])]
    #[Serializer\Groups(['group'])]
    #[Serializer\Type("ArrayCollection<RZ\Roadiz\CoreBundle\Entity\Role>")]
    private Collection $roleEntities;

    /**
     * @var array|null
     * @Serializer\Groups({"group", "user"})
     * @Serializer\Type("array<string>")
     */
    #[SymfonySerializer\Groups(['group', 'user'])]
    private ?array $roles = null;

    public function __construct()
    {
        $this->roleEntities = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * Get roles names as a simple array.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        if ($this->roles === null) {
            $this->roles = array_map(function (Role $role) {
                return $role->getRole();
            }, $this->getRolesEntities()->toArray());
        }

        return $this->roles;
    }

    /**
     * Get roles entities.
     *
     * @return Collection
     */
    public function getRolesEntities(): ?Collection
    {
        return $this->roleEntities;
    }

    /**
     * Get roles entities.
     *
     * @param Collection $roles
     *
     * @return Group
     */
    public function setRolesEntities(Collection $roles): self
    {
        $this->roleEntities = $roles;
        /** @var Role $role */
        foreach ($this->roleEntities as $role) {
            $role->addGroup($this);
        }
        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     * @deprecated Use addRoleEntity
     */
    public function addRole(Role $role): Group
    {
        return $this->addRoleEntity($role);
    }

    /**
     * @param Role $role
     *
     * @return $this
     */
    public function addRoleEntity(Role $role): Group
    {
        if (!$this->roleEntities->contains($role)) {
            $this->roleEntities->add($role);
        }

        return $this;
    }

    /**
     * @param Role $role
     * @return $this
     * @deprecated Use removeRoleEntity
     */
    public function removeRole(Role $role): Group
    {
        return $this->removeRoleEntity($role);
    }

    /**
     * @param Role $role
     *
     * @return $this
     */
    public function removeRoleEntity(Role $role): Group
    {
        if ($this->roleEntities->contains($role)) {
            $this->roleEntities->removeElement($role);
        }

        return $this;
    }
}
