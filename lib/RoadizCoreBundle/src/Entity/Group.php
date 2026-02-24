<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Repository\GroupRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A group gather User and Roles.
 */
#[ORM\Entity(repositoryClass: GroupRepository::class),
    ORM\Table(name: 'usergroups'),
    UniqueEntity(fields: ['name'])]
class Group extends AbstractEntity
{
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
     * @var Collection<int, Role>
     */
    #[ORM\JoinTable(name: 'groups_roles')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'groups', cascade: ['persist', 'merge'])]
    #[Serializer\Groups(['group'])]
    private Collection $roleEntities;

    #[Serializer\Groups(['group', 'user', 'group:export'])]
    private ?array $roles = null;

    public function __construct()
    {
        $this->roleEntities = new ArrayCollection();
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

    /**
     * Get roles names as a simple array.
     *
     * @return string[]
     */
    public function getRoles(): array
    {
        if (null === $this->roles) {
            $this->roles = array_map(function (Role $role) {
                return $role->getRole();
            }, $this->getRolesEntities()->toArray());
        }

        return $this->roles;
    }

    /**
     * Get roles entities.
     */
    public function getRolesEntities(): ?Collection
    {
        return $this->roleEntities;
    }

    /**
     * Get roles entities.
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
     * @return $this
     *
     * @deprecated Use addRoleEntity
     */
    public function addRole(Role $role): Group
    {
        return $this->addRoleEntity($role);
    }

    /**
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
     * @return $this
     *
     * @deprecated Use removeRoleEntity
     */
    public function removeRole(Role $role): Group
    {
        return $this->removeRoleEntity($role);
    }

    /**
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
