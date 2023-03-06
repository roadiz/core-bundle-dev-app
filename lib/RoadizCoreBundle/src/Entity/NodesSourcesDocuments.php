<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesDocumentsRepository;

/**
 * Describes a complex ManyToMany relation
 * between NodesSources, Documents and NodeTypeFields.
 */
#[
    ORM\Entity(repositoryClass: NodesSourcesDocumentsRepository::class),
    ORM\Table(name: "nodes_sources_documents"),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["ns_id", "node_type_field_id"], name: "nsdoc_field"),
    ORM\Index(columns: ["ns_id", "node_type_field_id", "position"], name: "nsdoc_field_position")
]
class NodesSourcesDocuments extends AbstractPositioned
{
    /**
     * @var NodesSources|null
     */
    #[ORM\ManyToOne(
        targetEntity: NodesSources::class,
        cascade: ['persist'],
        fetch: 'EAGER',
        inversedBy: 'documentsByFields'
    )]
    #[ORM\JoinColumn(name: 'ns_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?NodesSources $nodeSource;

    /**
     * @var Document|null
     */
    #[ORM\ManyToOne(
        targetEntity: Document::class,
        cascade: ['persist'],
        fetch: 'EAGER',
        inversedBy: 'nodesSourcesByFields'
    )]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?Document $document;

    /**
     * @var NodeTypeField|null
     */
    #[ORM\ManyToOne(targetEntity: NodeTypeField::class)]
    #[ORM\JoinColumn(name: 'node_type_field_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected ?NodeTypeField $field;

    /**
     * Create a new relation between NodeSource, a Document and a NodeTypeField.
     *
     * @param NodesSources  $nodeSource NodesSources and inherited types
     * @param Document      $document   Document to link
     * @param NodeTypeFieldInterface $field      NodeTypeField
     */
    public function __construct(NodesSources $nodeSource, Document $document, NodeTypeFieldInterface $field)
    {
        if (!$field instanceof NodeTypeField) {
            throw new \InvalidArgumentException('NodesSourcesDocuments field must be a NodeTypeField instance.');
        }
        $this->nodeSource = $nodeSource;
        $this->document = $document;
        $this->field = $field;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->nodeSource = null;
        }
    }

    /**
     * Gets the value of nodeSource.
     *
     * @return NodesSources|null
     */
    public function getNodeSource(): ?NodesSources
    {
        return $this->nodeSource;
    }

    /**
     * Sets the value of nodeSource.
     *
     * @param NodesSources|null $nodeSource the node source
     *
     * @return self
     */
    public function setNodeSource(?NodesSources $nodeSource): NodesSourcesDocuments
    {
        $this->nodeSource = $nodeSource;

        return $this;
    }

    /**
     * Gets the value of document.
     *
     * @return Document|null
     */
    public function getDocument(): ?Document
    {
        return $this->document;
    }

    /**
     * Sets the value of document.
     *
     * @param Document|null $document the document
     *
     * @return self
     */
    public function setDocument(?Document $document): NodesSourcesDocuments
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Gets the value of field.
     *
     * @return NodeTypeField|null
     */
    public function getField(): ?NodeTypeField
    {
        return $this->field;
    }

    /**
     * Sets the value of field.
     *
     * @param NodeTypeField|null $field the field
     *
     * @return self
     */
    public function setField(?NodeTypeField $field): NodesSourcesDocuments
    {
        $this->field = $field;

        return $this;
    }
}
