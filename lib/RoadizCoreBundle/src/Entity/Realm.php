<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Core\Serializer\Filter\PropertyFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\RealmRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter as BaseFilter;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Realm is an entity to describe authentication inside an area for API WebResponse.
 *
 * It supports plain_password (in query string), role-based and user-based authentication.
 * All behaviours except for serializationGroups are only applied when using API WebResponse.
 */
#[
    ORM\Entity(repositoryClass: RealmRepository::class),
    ORM\Table(name: "realms"),
    ORM\Index(columns: ["type"], name: "realms_type"),
    ORM\Index(columns: ["behaviour"], name: "realms_behaviour"),
    UniqueEntity(fields: ["name"]),
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\SearchFilter::class, properties: [
        "type" => "exact",
        "behaviour" => "exact",
        "name" => "exact"
    ])
]
class Realm extends AbstractEntity implements RealmInterface
{
    #[ORM\Column(name: 'type', type: 'string', length: 30)]
    #[SymfonySerializer\Groups(['get', 'realm'])]
    #[Serializer\Groups(['get', 'realm'])]
    private string $type = RealmInterface::TYPE_PLAIN_PASSWORD;

    #[ORM\Column(name: 'behaviour', type: 'string', length: 30, nullable: false, options: ['default' => 'none'])]
    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[Serializer\Groups(['get', 'realm', 'web_response'])]
    private string $behaviour = RealmInterface::BEHAVIOUR_NONE;

    #[ORM\Column(name: 'name', unique: true)]
    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[Serializer\Groups(['get', 'realm', 'web_response'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 250)]
    #[Assert\Regex('#^[\w\s]+$#u')]
    private string $name = '';

    /**
     * @var string|null
     * @Serializer\Exclude()
     */
    #[ORM\Column(name: 'plain_password', unique: false, type: 'string', length: 255, nullable: true)]
    #[SymfonySerializer\Ignore]
    private ?string $plainPassword = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private ?Role $roleEntity = null;

    #[ORM\Column(name: 'serialization_group', type: 'string', length: 200, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private ?string $serializationGroup = null;

    /**
     * @var Collection<UserInterface>
     */
    #[ORM\JoinTable(name: 'realms_users')]
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $users;

    /**
     * @var Collection<RealmNode>
     */
    #[ORM\OneToMany(mappedBy: 'realm', targetEntity: RealmNode::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $realmNodes;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->realmNodes = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        if (null === $this->roleEntity) {
            return null;
        }
        return $this->roleEntity->getRole();
    }

    /**
     * @return Role|null
     */
    public function getRoleEntity(): ?Role
    {
        return $this->roleEntity;
    }

    /**
     * @param Role|null $roleEntity
     * @return Realm
     */
    public function setRoleEntity(?Role $roleEntity): Realm
    {
        $this->roleEntity = $roleEntity;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSerializationGroup(): ?string
    {
        return $this->serializationGroup;
    }

    /**
     * @param string|null $serializationGroup
     * @return Realm
     */
    public function setSerializationGroup(?string $serializationGroup): Realm
    {
        $this->serializationGroup = null !== $serializationGroup ?
            (new AsciiSlugger())->slug($serializationGroup, '_')->lower()->toString() :
            (new AsciiSlugger())->slug($this->getName(), '_')->lower()->toString();
        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? '';
    }

    /**
     * @param string $name
     * @return Realm
     */
    public function setName(string $name): Realm
    {
        $this->name = $name;
        if (null === $this->serializationGroup) {
            $this->serializationGroup = (new AsciiSlugger())->slug($this->name, '_')->lower()->toString();
        }
        return $this;
    }

    /**
     * @return ArrayCollection<RealmNode>|Collection<RealmNode>
     */
    public function getRealmNodes(): Collection
    {
        return $this->realmNodes;
    }

    /**
     * @param ArrayCollection<RealmNode>|Collection<RealmNode> $realmNodes
     * @return Realm
     */
    public function setRealmNodes(Collection $realmNodes)
    {
        $this->realmNodes = $realmNodes;
        return $this;
    }

    /**
     * @return Collection<UserInterface>|ArrayCollection<UserInterface>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<UserInterface>|ArrayCollection<UserInterface> $users
     * @return Realm
     */
    public function setUsers(Collection $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     * @return Realm
     */
    public function setPlainPassword(?string $plainPassword): Realm
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    /**
     * @return string
     */
    public function getBehaviour(): string
    {
        return $this->behaviour;
    }

    /**
     * @param string $behaviour
     * @return Realm
     */
    public function setBehaviour(string $behaviour): Realm
    {
        $this->behaviour = $behaviour;
        return $this;
    }

    public function getChallenge(): string
    {
        return $this->getAuthenticationScheme() . ' realm="' . addslashes($this->getName()) . '"';
    }

    /**
     * @return string
     */
    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[Serializer\Groups(['get', 'realm', 'web_response'])]
    public function getAuthenticationScheme(): string
    {
        switch ($this->getType()) {
            case RealmInterface::TYPE_PLAIN_PASSWORD:
                return 'PasswordQuery';
            default:
                return 'Bearer';
        }
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Realm
     */
    public function setType(string $type): Realm
    {
        $this->type = $type;
        return $this;
    }
}
