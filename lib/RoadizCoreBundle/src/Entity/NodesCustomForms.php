<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Repository\NodesCustomFormsRepository;

/**
 * Describes a complex ManyToMany relation
 * between Nodes, CustomForms and NodeTypeFields.
 */
#[
    ORM\Entity(repositoryClass: NodesCustomFormsRepository::class),
    ORM\Table(name: "nodes_custom_forms"),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["node_id", "position"], name: "customform_node_position"),
    ORM\Index(columns: ["node_id", "node_type_field_id", "position"], name: "customform_node_field_position")
]
class NodesCustomForms extends AbstractPositioned
{
    #[ORM\ManyToOne(targetEntity: Node::class, fetch: 'EAGER', inversedBy: 'customForms')]
    #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Node $node;

    #[ORM\ManyToOne(targetEntity: CustomForm::class, fetch: 'EAGER', inversedBy: 'nodes')]
    #[ORM\JoinColumn(name: 'custom_form_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected CustomForm $customForm;

    #[ORM\ManyToOne(targetEntity: NodeTypeField::class)]
    #[ORM\JoinColumn(name: 'node_type_field_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected NodeTypeField $field;

    /**
     * Create a new relation between a Node, a CustomForm and a NodeTypeField.
     *
     * @param Node $node
     * @param CustomForm $customForm
     * @param NodeTypeFieldInterface $field NodeTypeField
     */
    public function __construct(Node $node, CustomForm $customForm, NodeTypeFieldInterface $field)
    {
        if (!$field instanceof NodeTypeField) {
            throw new \InvalidArgumentException('NodesCustomForms only accept NodeTypeField');
        }
        $this->node = $node;
        $this->customForm = $customForm;
        $this->field = $field;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Gets the value of node.
     *
     * @return Node
     */
    public function getNode(): Node
    {
        return $this->node;
    }

    /**
     * Sets the value of node.
     *
     * @param Node $node the node
     *
     * @return self
     */
    public function setNode(Node $node): NodesCustomForms
    {
        $this->node = $node;
        return $this;
    }

    /**
     * Gets the value of customForm.
     *
     * @return CustomForm
     */
    public function getCustomForm(): CustomForm
    {
        return $this->customForm;
    }

    /**
     * Sets the value of customForm.
     *
     * @param CustomForm $customForm the custom form
     *
     * @return self
     */
    public function setCustomForm(CustomForm $customForm): NodesCustomForms
    {
        $this->customForm = $customForm;

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
    public function setField(NodeTypeField $field): NodesCustomForms
    {
        $this->field = $field;

        return $this;
    }
}
