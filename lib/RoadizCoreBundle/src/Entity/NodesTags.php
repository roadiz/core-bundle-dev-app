<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Comparable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedTrait;
use RZ\Roadiz\CoreBundle\Repository\NodesTagsRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

#[
    ORM\Entity(repositoryClass: NodesTagsRepository::class),
    ORM\Table(name: 'nodes_tags'),
    ORM\Index(columns: ['node_id', 'position'], name: 'nodes_tags_node_id_position'),
    ORM\Index(columns: ['tag_id', 'position'], name: 'nodes_tags_tag_id_position'),
    ORM\Index(columns: ['position'], name: 'nodes_tags_position'),
    ORM\Index(columns: ['tag_id'], name: 'nodes_tags_tag_id'),
    ORM\Index(columns: ['node_id'], name: 'nodes_tags_node_id'),
]
class NodesTags implements PositionedInterface, Comparable
{
    use PositionedTrait;

    #[
        ORM\Id,
        ORM\Column(type: 'string', length: 36),
        SymfonySerializer\Ignore
    ]
    /** @phpstan-ignore-next-line */
    protected ?string $id = null;

    #[
        ORM\ManyToOne(targetEntity: Node::class, inversedBy: 'nodesTags'),
        ORM\JoinColumn(
            name: 'node_id',
            referencedColumnName: 'id',
            unique: false,
            nullable: false,
            onDelete: 'CASCADE'
        ),
        SymfonySerializer\Ignore,
        Serializer\Exclude,
    ]
    private Node $node;

    #[
        ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'nodesTags'),
        ORM\JoinColumn(
            name: 'tag_id',
            referencedColumnName: 'id',
            unique: false,
            nullable: false,
            onDelete: 'CASCADE'
        ),
        SymfonySerializer\Groups(['nodes_sources', 'nodes_sources_base', 'node']),
        Serializer\Groups(['nodes_sources', 'nodes_sources_base', 'node']),
    ]
    private Tag $tag;

    #[
        ORM\Column(type: 'float', nullable: false, options: ['default' => 1]),
        SymfonySerializer\Ignore,
        Serializer\Exclude,
    ]
    protected float $position = 0.0;

    public function __construct(?string $uuid = null)
    {
        $this->id = $uuid ?? \Ramsey\Uuid\Uuid::uuid4()->toString();
    }

    public function getNode(): Node
    {
        return $this->node;
    }

    public function setNode(Node $node): NodesTags
    {
        $this->node = $node;

        return $this;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): NodesTags
    {
        $this->tag = $tag;

        return $this;
    }
}
