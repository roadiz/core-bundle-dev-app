<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\AbstractPositioned;
use RZ\Roadiz\CoreBundle\Repository\TagTranslationDocumentsRepository;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Describes a complex ManyToMany relation
 * between TagTranslation and Documents.
 */
#[
    ORM\Entity(repositoryClass: TagTranslationDocumentsRepository::class),
    ORM\Table(name: 'tags_translations_documents'),
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['tag_translation_id', 'position'], name: 'tagtranslation_position')
]
class TagTranslationDocuments extends AbstractPositioned
{
    /**
     * Create a new relation between NodeSource, a Document and a NodeTypeField.
     */
    public function __construct(
        #[ORM\ManyToOne(
            targetEntity: TagTranslation::class,
            cascade: ['persist', 'merge'],
            fetch: 'EAGER',
            inversedBy: 'tagTranslationDocuments'
        )]
        #[ORM\JoinColumn(name: 'tag_translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[SymfonySerializer\Ignore]
        protected TagTranslation $tagTranslation,
        #[ORM\ManyToOne(
            targetEntity: Document::class,
            cascade: ['persist', 'merge'],
            fetch: 'EAGER',
            inversedBy: 'tagTranslations'
        )]
        #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[SymfonySerializer\Groups(['tag'])]
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

    public function setDocument(Document $document): TagTranslationDocuments
    {
        $this->document = $document;

        return $this;
    }

    public function getTagTranslation(): TagTranslation
    {
        return $this->tagTranslation;
    }

    public function setTagTranslation(TagTranslation $tagTranslation): TagTranslationDocuments
    {
        $this->tagTranslation = $tagTranslation;

        return $this;
    }
}
