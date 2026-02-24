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
#[ORM\Entity(repositoryClass: NodesCustomFormsRepository::class),
    ORM\Table(name: 'nodes_custom_forms'),
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['node_id', 'position'], name: 'customform_node_position'),
    ORM\Index(columns: ['node_id', 'field_name', 'position'], name: 'customform_node_field_position')]
class NodesCustomForms extends AbstractPositioned
{
    use FieldAwareEntityTrait;

    #[ORM\ManyToOne(targetEntity: Node::class, fetch: 'EAGER', inversedBy: 'customForms')]
    #[ORM\JoinColumn(name: 'node_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Node $node;

    #[ORM\ManyToOne(targetEntity: CustomForm::class, fetch: 'EAGER', inversedBy: 'nodes')]
    #[ORM\JoinColumn(name: 'custom_form_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected CustomForm $customForm;

    /**
     * Create a new relation between a Node, a CustomForm and a NodeTypeFieldInterface.
     */
    public function __construct(Node $node, CustomForm $customForm, ?NodeTypeFieldInterface $field = null)
    {
        $this->node = $node;
        $this->customForm = $customForm;
        $this->initializeFieldAwareEntityTrait($field);
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Gets the value of node.
     */
    public function getNode(): Node
    {
        return $this->node;
    }

    /**
     * Sets the value of node.
     *
     * @param Node $node the node
     */
    public function setNode(Node $node): NodesCustomForms
    {
        $this->node = $node;

        return $this;
    }

    /**
     * Gets the value of customForm.
     */
    public function getCustomForm(): CustomForm
    {
        return $this->customForm;
    }

    /**
     * Sets the value of customForm.
     *
     * @param CustomForm $customForm the custom form
     */
    public function setCustomForm(CustomForm $customForm): NodesCustomForms
    {
        $this->customForm = $customForm;

        return $this;
    }
}
