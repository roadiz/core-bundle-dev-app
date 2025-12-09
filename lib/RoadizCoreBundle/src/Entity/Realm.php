<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter as BaseFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Repository\RealmRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
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
    ORM\Table(name: 'realms'),
    ORM\Index(columns: ['type'], name: 'realms_type'),
    ORM\Index(columns: ['behaviour'], name: 'realms_behaviour'),
    UniqueEntity(fields: ['name']),
    ApiFilter(PropertyFilter::class),
    ApiFilter(BaseFilter\SearchFilter::class, properties: [
        'type' => 'exact',
        'behaviour' => 'exact',
        'name' => 'exact',
    ])
]
class Realm implements RealmInterface
{
    use SequentialIdTrait;

    #[ORM\Column(name: 'type', type: 'string', length: 30)]
    #[SymfonySerializer\Groups(['get', 'realm'])]
    #[Assert\Length(max: 30)]
    private string $type = RealmInterface::TYPE_PLAIN_PASSWORD;

    #[ORM\Column(name: 'behaviour', type: 'string', length: 30, nullable: false, options: ['default' => 'none'])]
    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[Assert\Length(max: 30)]
    private string $behaviour = RealmInterface::BEHAVIOUR_NONE;

    #[ORM\Column(name: 'name', unique: true)]
    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 250)]
    #[Assert\Regex('#^[\w\s]+$#u')]
    private string $name = '';

    #[ORM\Column(name: 'plain_password', type: 'string', length: 255, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 255)]
    private ?string $plainPassword = null;

    #[ORM\Column(name: 'role', type: 'string', length: 50, unique: false, nullable: true)]
    #[SymfonySerializer\Ignore]
    private ?string $role = null;

    #[ORM\Column(name: 'serialization_group', type: 'string', length: 200, nullable: true)]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 200)]
    private ?string $serializationGroup = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\JoinTable(name: 'realms_users')]
    #[ORM\ManyToMany(targetEntity: User::class)]
    #[SymfonySerializer\Ignore]
    private Collection $users;

    /**
     * @var Collection<int, RealmNode>
     */
    #[ORM\OneToMany(mappedBy: 'realm', targetEntity: RealmNode::class)]
    #[SymfonySerializer\Ignore]
    private Collection $realmNodes;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->realmNodes = new ArrayCollection();
    }

    #[\Override]
    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): Realm
    {
        $this->role = $role;

        return $this;
    }

    #[\Override]
    public function getSerializationGroup(): ?string
    {
        return $this->serializationGroup;
    }

    public function setSerializationGroup(?string $serializationGroup): Realm
    {
        $this->serializationGroup = null !== $serializationGroup ?
            (new AsciiSlugger())->slug($serializationGroup, '_')->lower()->toString() :
            null;

        return $this;
    }

    #[\Override]
    public function getName(): string
    {
        return $this->name ?? '';
    }

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
     */
    public function setRealmNodes(Collection $realmNodes): Realm
    {
        $this->realmNodes = $realmNodes;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    #[\Override]
    public function getUsers(): Collection
    {
        return $this->users;
    }

    /**
     * @param Collection<int, User> $users
     */
    public function setUsers(Collection $users): Realm
    {
        $this->users = $users;

        return $this;
    }

    #[\Override]
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): Realm
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    #[\Override]
    public function getBehaviour(): string
    {
        return $this->behaviour;
    }

    public function setBehaviour(string $behaviour): Realm
    {
        $this->behaviour = $behaviour;

        return $this;
    }

    #[\Override]
    public function getChallenge(): string
    {
        return $this->getAuthenticationScheme().' realm="'.addslashes($this->getName()).'"';
    }

    #[SymfonySerializer\Groups(['get', 'realm', 'web_response'])]
    #[\Override]
    public function getAuthenticationScheme(): string
    {
        return match ($this->getType()) {
            RealmInterface::TYPE_PLAIN_PASSWORD => 'PasswordQuery',
            default => 'Bearer',
        };
    }

    #[\Override]
    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): Realm
    {
        $this->type = $type;

        return $this;
    }
}
