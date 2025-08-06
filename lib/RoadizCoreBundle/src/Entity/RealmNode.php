<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Repository\RealmNodeRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

#[
    ORM\Entity(repositoryClass: RealmNodeRepository::class),
    ORM\Table(name: 'realms_nodes'),
    ORM\Index(columns: ['inheritance_type'], name: 'realms_nodes_inheritance_type'),
    ORM\Index(columns: ['realm_id'], name: 'realms_nodes_realm'),
    ORM\Index(columns: ['node_id'], name: 'realms_nodes_node'),
    ORM\Index(columns: ['node_id', 'inheritance_type'], name: 'realms_nodes_node_inheritance_type'),
    ORM\UniqueConstraint(name: 'realms_nodes_unique', columns: ['node_id', 'realm_id']),
    UniqueEntity(fields: ['node', 'realm'])
]
class RealmNode implements PersistableInterface
{
    use SequentialIdTrait;

    #[ORM\ManyToOne(targetEntity: Node::class)]
    #[ORM\JoinColumn(
        name: 'node_id',
        referencedColumnName: 'id',
        unique: false,
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[SymfonySerializer\Ignore]
    private Node $node;

    #[ORM\ManyToOne(targetEntity: Realm::class, inversedBy: 'realmNodes')]
    #[ORM\JoinColumn(
        name: 'realm_id',
        referencedColumnName: 'id',
        unique: false,
        nullable: false,
        onDelete: 'CASCADE'
    )]
    #[SymfonySerializer\Ignore]
    private Realm $realm;

    #[ORM\Column(name: 'inheritance_type', type: 'string', length: 10, nullable: false)]
    #[SymfonySerializer\Ignore]
    #[Assert\Length(max: 10)]
    private string $inheritanceType = RealmInterface::INHERITANCE_AUTO;

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): RealmNode
    {
        $this->node = $node;

        return $this;
    }

    public function getRealm(): Realm
    {
        return $this->realm;
    }

    public function setRealm(Realm $realm): RealmNode
    {
        $this->realm = $realm;

        return $this;
    }

    public function getInheritanceType(): string
    {
        return $this->inheritanceType;
    }

    public function setInheritanceType(string $inheritanceType): RealmNode
    {
        $this->inheritanceType = $inheritanceType;

        return $this;
    }
}
