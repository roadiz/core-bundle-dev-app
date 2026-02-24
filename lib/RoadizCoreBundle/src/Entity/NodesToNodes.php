<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Repository\NodesToNodesRepository;

/**
 * Describes a complex ManyToMany relation
 * between two Nodes and NodeTypeFields.
 */
#[ORM\Entity(repositoryClass: NodesToNodesRepository::class),
    ORM\Table(name: 'nodes_to_nodes'),
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['node_a_id', 'field_name'], name: 'node_a_field'),
    ORM\Index(columns: ['node_a_id', 'field_name', 'position'], name: 'node_a_field_position'),
    ORM\Index(columns: ['node_b_id', 'field_name'], name: 'node_b_field'),
    ORM\Index(columns: ['node_b_id', 'field_name', 'position'], name: 'node_b_field_position')]
class NodesToNodes extends AbstractPositioned
{
    use FieldAwareEntityTrait;

    #[ORM\ManyToOne(targetEntity: Node::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'bNodes')]
    #[ORM\JoinColumn(name: 'node_a_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Node $nodeA;

    #[ORM\ManyToOne(targetEntity: Node::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'aNodes')]
    #[ORM\JoinColumn(name: 'node_b_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Node $nodeB;

    public function __construct(Node $nodeA, Node $nodeB, ?NodeTypeFieldInterface $field = null)
    {
        $this->nodeA = $nodeA;
        $this->nodeB = $nodeB;
        $this->initializeFieldAwareEntityTrait($field);
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Gets the value of nodeA.
     */
    public function getNodeA(): Node
    {
        return $this->nodeA;
    }

    /**
     * Sets the value of nodeA.
     *
     * @param Node $nodeA the node
     */
    public function setNodeA(Node $nodeA): NodesToNodes
    {
        $this->nodeA = $nodeA;

        return $this;
    }

    /**
     * Gets the value of nodeB.
     */
    public function getNodeB(): Node
    {
        return $this->nodeB;
    }

    /**
     * Sets the value of nodeB.
     *
     * @param Node $nodeB the node
     */
    public function setNodeB(Node $nodeB): NodesToNodes
    {
        $this->nodeB = $nodeB;

        return $this;
    }
}
