<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\CoreBundle\Repository\TagTranslationDocumentsRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Describes a complex ManyToMany relation
 * between TagTranslation and Documents.
 */
#[
    ORM\Entity(repositoryClass: TagTranslationDocumentsRepository::class),
    ORM\Table(name: "tags_translations_documents"),
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["tag_translation_id", "position"], name: "tagtranslation_position")
]
class TagTranslationDocuments extends AbstractPositioned
{
    #[ORM\ManyToOne(
        targetEntity: TagTranslation::class,
        cascade: ['persist', 'merge'],
        fetch: 'EAGER',
        inversedBy: 'tagTranslationDocuments'
    )]
    #[ORM\JoinColumn(name: 'tag_translation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected ?TagTranslation $tagTranslation = null;

    #[ORM\ManyToOne(
        targetEntity: Document::class,
        cascade: ['persist', 'merge'],
        fetch: 'EAGER',
        inversedBy: 'tagTranslations'
    )]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['tag'])]
    #[Serializer\Groups(['tag'])]
    protected ?Document $document = null;

    /**
     * Create a new relation between NodeSource, a Document and a NodeTypeField.
     *
     * @param TagTranslation|null $tagTranslation
     * @param Document|null $document
     */
    public function __construct(TagTranslation $tagTranslation = null, Document $document = null)
    {
        $this->document = $document;
        $this->tagTranslation = $tagTranslation;
    }

    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $this->tagTranslation = null;
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
     * @return self
     */
    public function setDocument(?Document $document): TagTranslationDocuments
    {
        $this->document = $document;

        return $this;
    }

    public function getTagTranslation(): ?TagTranslation
    {
        return $this->tagTranslation;
    }

    /**
     * @param TagTranslation|null $tagTranslation
     * @return TagTranslationDocuments
     */
    public function setTagTranslation(?TagTranslation $tagTranslation): TagTranslationDocuments
    {
        $this->tagTranslation = $tagTranslation;
        return $this;
    }
}
