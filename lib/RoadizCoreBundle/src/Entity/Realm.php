<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Repository\RealmRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\String\Slugger\AsciiSlugger;
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
    #[Assert\Length(max: 30)]
    private string $type = RealmInterface::TYPE_PLAIN_PASSWORD;

    #[ORM\Column(name: 'behaviour', type: 'string', length: 30, nullable: false, options: ['default' => 'none'])]
    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[Serializer\Groups(['get', 'realm', 'web_response'])]
    #[Assert\Length(max: 30)]
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
    #[ORM\Column(name: 'plain_password', type: 'string', length: 255, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 255)]
    private ?string $plainPassword = null;

    #[ORM\ManyToOne(targetEntity: Role::class)]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private ?Role $roleEntity = null;

    #[ORM\Column(name: 'serialization_group', type: 'string', length: 200, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    #[Assert\Length(max: 200)]
    private ?string $serializationGroup = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\JoinTable(name: 'realms_users')]
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    private Collection $users;

    /**
     * @var Collection<int, RealmNode>
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
     * @return Collection<int, RealmNode>
     */
    public function getRealmNodes(): Collection
    {
        return $this->realmNodes;
    }

    /**
     * @param Collection<int, RealmNode> $realmNodes
     * @return Realm
     */
    public function setRealmNodes(Collection $realmNodes)
    {
        $this->realmNodes = $realmNodes;
        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<int, User> $users
     * @return Realm
     */
    public function setUsers(Collection $users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param string|null $plainPassword
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
