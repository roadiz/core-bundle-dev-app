<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedTrait;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesDocumentsRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Describes a complex ManyToMany relation
 * between NodesSources, Documents and NodeTypeFields.
 */
#[
    ORM\Entity(repositoryClass: NodesSourcesDocumentsRepository::class),
    ORM\Table(name: 'nodes_sources_documents'),
    ORM\HasLifecycleCallbacks,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['ns_id', 'field_name'], name: 'nsdoc_field'),
    ORM\Index(columns: ['ns_id', 'field_name', 'position'], name: 'nsdoc_field_position')
]
class NodesSourcesDocuments implements PositionedInterface, PersistableInterface
{
    use SequentialIdTrait;
    use PositionedTrait;
    use FieldAwareEntityTrait;

    /**
     * @var string|null Image crop alignment.
     *
     * The possible values are:
     *
     * top-left
     * top
     * top-right
     * left
     * center (default)
     * right
     * bottom-left
     * bottom
     * bottom-right
     */
    #[ORM\Column(name: 'image_crop_alignment', type: 'string', length: 12, nullable: true)]
    #[Assert\Length(max: 12)]
    #[Assert\Choice(choices: [
        'top-left',
        'top',
        'top-right',
        'left',
        'center',
        'right',
        'bottom-left',
        'bottom',
        'bottom-right',
    ])]
    protected ?string $imageCropAlignment = null;

    #[ORM\Column(name: 'hotspot', type: 'json', nullable: true)]
    protected ?array $hotspot = null;

    /**
     * Create a new relation between NodeSource, a Document for a NodeTypeField.
     */
    public function __construct(
        #[ORM\ManyToOne(
            targetEntity: NodesSources::class,
            cascade: ['persist'],
            fetch: 'EAGER',
            inversedBy: 'documentsByFields'
        )]
        #[Assert\NotNull]
        #[ORM\JoinColumn(name: 'ns_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        protected NodesSources $nodeSource,
        #[ORM\ManyToOne(
            targetEntity: Document::class,
            cascade: ['persist'],
            fetch: 'EAGER',
            inversedBy: 'nodesSourcesByFields'
        )]
        #[Assert\NotNull]
        #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        protected Document $document,
        ?NodeTypeFieldInterface $field = null,
    ) {
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

    public function getImageCropAlignment(): ?string
    {
        return $this->imageCropAlignment;
    }

    public function setImageCropAlignment(?string $imageCropAlignment): NodesSourcesDocuments
    {
        $this->imageCropAlignment = $imageCropAlignment;

        return $this;
    }

    public function getHotspot(): ?array
    {
        return $this->hotspot;
    }

    public function setHotspot(?array $hotspot): NodesSourcesDocuments
    {
        $this->hotspot = $hotspot;

        return $this;
    }

    public function copyFrom(NodesSourcesDocuments $source): NodesSourcesDocuments
    {
        $this->setDocument($source->getDocument());
        $this->setHotspot($source->getHotspot());
        $this->setImageCropAlignment($source->getImageCropAlignment());

        return $this;
    }
}
