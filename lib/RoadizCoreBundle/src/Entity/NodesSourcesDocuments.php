<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesDocumentsRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes a complex ManyToMany relation
 * between NodesSources, Documents and NodeTypeFields.
 */
#[ORM\Entity(repositoryClass: NodesSourcesDocumentsRepository::class),
    ORM\Table(name: 'nodes_sources_documents'),
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['ns_id', 'field_name'], name: 'nsdoc_field'),
    ORM\Index(columns: ['ns_id', 'field_name', 'position'], name: 'nsdoc_field_position')]
class NodesSourcesDocuments extends AbstractPositioned
{
    use FieldAwareEntityTrait;

    #[ORM\ManyToOne(
        targetEntity: NodesSources::class,
        cascade: ['persist'],
        fetch: 'EAGER',
        inversedBy: 'documentsByFields'
    )]
    #[Assert\NotNull]
    #[ORM\JoinColumn(name: 'ns_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected NodesSources $nodeSource;

    #[ORM\ManyToOne(
        targetEntity: Document::class,
        cascade: ['persist'],
        fetch: 'EAGER',
        inversedBy: 'nodesSourcesByFields'
    )]
    #[Assert\NotNull]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    protected Document $document;

    /**
     * Create a new relation between NodeSource, a Document and a NodeTypeFieldInterface.
     */
    public function __construct(NodesSources $nodeSource, Document $document, ?NodeTypeFieldInterface $field = null)
    {
        $this->nodeSource = $nodeSource;
        $this->document = $document;
        $this->initializeFieldAwareEntityTrait($field);
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    /**
     * Gets the value of nodeSource.
     */
    public function getNodeSource(): NodesSources
    {
        return $this->nodeSource;
    }

    /**
     * Sets the value of nodeSource.
     *
     * @param NodesSources $nodeSource the node source
     */
    public function setNodeSource(NodesSources $nodeSource): NodesSourcesDocuments
    {
        $this->nodeSource = $nodeSource;

        return $this;
    }

    /**
     * Gets the value of document.
     */
    public function getDocument(): Document
    {
        return $this->document;
    }

    /**
     * Sets the value of document.
     *
     * @param Document $document the document
     */
    public function setDocument(Document $document): NodesSourcesDocuments
    {
        $this->document = $document;

        return $this;
    }
}
