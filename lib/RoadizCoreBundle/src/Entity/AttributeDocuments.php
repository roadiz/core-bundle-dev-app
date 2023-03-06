<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\AttributeDocumentsRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Describes a complex ManyToMany relation
 * between Attribute and Documents.
 */
#[
    ORM\Entity(repositoryClass: AttributeDocumentsRepository::class),
    ORM\Table(name: "attributes_documents"),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["attribute_id", "position"])
]
class AttributeDocuments extends AbstractPositioned
{
    #[
        ORM\ManyToOne(
            targetEntity: Attribute::class,
            cascade: ["persist", "merge"],
            fetch: "EAGER",
            inversedBy: "attributeDocuments"
        ),
        ORM\JoinColumn(
            name: "attribute_id",
            referencedColumnName: "id",
            onDelete: "CASCADE"
        ),
        Serializer\Exclude(),
        SymfonySerializer\Ignore()
    ]
    protected ?Attribute $attribute = null;

    #[
        ORM\ManyToOne(
            targetEntity: Document::class,
            cascade: ["persist", "merge"],
            fetch: "EAGER",
            inversedBy: "attributeDocuments"
        ),
        ORM\JoinColumn(
            name: "document_id",
            referencedColumnName: "id",
            onDelete: "CASCADE"
        ),
        Serializer\Groups(["attribute"]),
        SymfonySerializer\Groups(["attribute"]),
        Serializer\Type(Document::class)
    ]
    protected ?Document $document = null;

    /**
     * @param Attribute|null $attribute
     * @param Document|null $document
     */
    public function __construct(Attribute $attribute = null, Document $document = null)
    {
        $this->document = $document;
        $this->attribute = $attribute;
    }

    /**
     *
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->attribute = null;
        }
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
     * @return AttributeDocuments
     */
    public function setDocument(?Document $document): AttributeDocuments
    {
        $this->document = $document;

        return $this;
    }

    /**
     * @return Attribute|null
     */
    public function getAttribute(): ?Attribute
    {
        return $this->attribute;
    }

    /**
     * @param Attribute|null $attribute
     * @return AttributeDocuments
     */
    public function setAttribute(?Attribute $attribute): AttributeDocuments
    {
        $this->attribute = $attribute;
        return $this;
    }
}
