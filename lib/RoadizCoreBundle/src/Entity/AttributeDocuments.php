<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedTrait;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\CoreBundle\Repository\AttributeDocumentsRepository;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;

/**
 * Describes a complex ManyToMany relation
 * between Attribute and Documents.
 */
#[ORM\Entity(repositoryClass: AttributeDocumentsRepository::class),
    ORM\Table(name: 'attributes_documents'),
    ORM\HasLifecycleCallbacks,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['attribute_id', 'position'])]
class AttributeDocuments implements PositionedInterface, PersistableInterface
{
    use SequentialIdTrait;
    use PositionedTrait;

    public function __construct(
        #[ORM\ManyToOne(
            targetEntity: Attribute::class,
            cascade: ['persist', 'merge'],
            fetch: 'EAGER',
            inversedBy: 'attributeDocuments'
        ),
            ORM\JoinColumn(
                name: 'attribute_id',
                referencedColumnName: 'id',
                nullable: false,
                onDelete: 'CASCADE'
            ),
            SymfonySerializer\Ignore()]
        protected Attribute $attribute,
        #[ORM\ManyToOne(
            targetEntity: Document::class,
            cascade: ['persist', 'merge'],
            fetch: 'EAGER',
            inversedBy: 'attributeDocuments'
        ),
            ORM\JoinColumn(
                name: 'document_id',
                referencedColumnName: 'id',
                nullable: false,
                onDelete: 'CASCADE'
            ),
            SymfonySerializer\Groups(['attribute']),]
        protected Document $document,
    ) {
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
        }
    }

    public function getDocument(): Document
    {
        return $this->document;
    }

    public function setDocument(Document $document): AttributeDocuments
    {
        $this->document = $document;

        return $this;
    }

    public function getAttribute(): Attribute
    {
        return $this->attribute;
    }

    public function setAttribute(Attribute $attribute): AttributeDocuments
    {
        $this->attribute = $attribute;

        return $this;
    }
}
