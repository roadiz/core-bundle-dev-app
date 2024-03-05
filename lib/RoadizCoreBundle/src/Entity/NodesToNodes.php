<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Repository\NodesToNodesRepository;

/**
 * Describes a complex ManyToMany relation
 * between two Nodes and NodeTypeFields.
 */
#[
    ORM\Entity(repositoryClass: NodesToNodesRepository::class),
    ORM\Table(name: "nodes_to_nodes"),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["node_a_id", "node_type_field_id"], name: "node_a_field"),
    ORM\Index(columns: ["node_a_id", "node_type_field_id", "position"], name: "node_a_field_position"),
    ORM\Index(columns: ["node_b_id", "node_type_field_id"], name: "node_b_field"),
    ORM\Index(columns: ["node_b_id", "node_type_field_id", "position"], name: "node_b_field_position")
]
class NodesToNodes extends AbstractPositioned
{
    /**
     * @var Node
     */
    #[ORM\ManyToOne(targetEntity: Node::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'bNodes')]
    #[ORM\JoinColumn(name: 'node_a_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Node $nodeA;

    /**
     * @var Node
     */
    #[ORM\ManyToOne(targetEntity: Node::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'aNodes')]
    #[ORM\JoinColumn(name: 'node_b_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Node $nodeB;

    /**
     * @var NodeTypeField
     */
    #[ORM\ManyToOne(targetEntity: NodeTypeField::class)]
    #[ORM\JoinColumn(name: 'node_type_field_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected NodeTypeField $field;

    /**
     * Create a new relation between two Nodes and a NodeTypeField.
     *
     * @param Node          $nodeA
     * @param Node          $nodeB
     * @param NodeTypeField $field NodeTypeField
     */
    public function __construct(Node $nodeA, Node $nodeB, NodeTypeField $field)
    {
        $this->nodeA = $nodeA;
        $this->nodeB = $nodeB;
        $this->field = $field;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Gets the value of nodeA.
     *
     * @return Node
     */
    public function getNodeA(): Node
    {
        return $this->nodeA;
    }

    /**
     * Sets the value of nodeA.
     *
     * @param Node $nodeA the node
     *
     * @return self
     */
    public function setNodeA(Node $nodeA): NodesToNodes
    {
        $this->nodeA = $nodeA;

        return $this;
    }

    /**
     * Gets the value of nodeB.
     *
     * @return Node
     */
    public function getNodeB(): Node
    {
        return $this->nodeB;
    }

    /**
     * Sets the value of nodeB.
     *
     * @param Node $nodeB the node
     *
     * @return self
     */
    public function setNodeB(Node $nodeB): NodesToNodes
    {
        $this->nodeB = $nodeB;

        return $this;
    }

    /**
     * Gets the value of field.
     *
     * @return NodeTypeField
     */
    public function getField(): NodeTypeField
    {
        return $this->field;
    }

    /**
     * Sets the value of field.
     *
     * @param NodeTypeField $field the field
     *
     * @return self
     */
    public function setField(NodeTypeField $field): NodesToNodes
    {
        $this->field = $field;

        return $this;
    }
}
