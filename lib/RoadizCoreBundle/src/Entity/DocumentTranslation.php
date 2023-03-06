<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Loggable;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractEntity;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\DocumentTranslationRepository;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

#[
    ORM\Entity(repositoryClass: DocumentTranslationRepository::class),
    ORM\Table(name: "documents_translations"),
    ORM\UniqueConstraint(columns: ["document_id", "translation_id"]),
    Gedmo\Loggable(logEntryClass: UserLogEntry::class)
]
class DocumentTranslation extends AbstractEntity implements Loggable
{
    #[ORM\Column(type: 'string', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Gedmo\Versioned]
    protected ?string $name = null;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Gedmo\Versioned]
    protected ?string $description = null;

    #[ORM\Column(type: 'text', length: 2000, nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Gedmo\Versioned]
    protected ?string $externalUrl = null;

    #[ORM\ManyToOne(targetEntity: Translation::class, fetch: 'EXTRA_LAZY', inversedBy: 'documentTranslations')]
    #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    protected ?TranslationInterface $translation = null;

    #[ORM\ManyToOne(targetEntity: Document::class, fetch: 'EXTRA_LAZY', inversedBy: 'documentTranslations')]
    #[ORM\JoinColumn(name: 'document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[SymfonySerializer\Ignore]
    #[Serializer\Exclude]
    protected ?DocumentInterface $document;

    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Serializer\Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[Gedmo\Versioned]
    private ?string $copyright = null;

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): DocumentTranslation
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     *
     * @return $this
     */
    public function setDescription(?string $description): DocumentTranslation
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    /**
     * @param string|null $copyright
     *
     * @return $this
     */
    public function setCopyright(?string $copyright): DocumentTranslation
    {
        $this->copyright = $copyright;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExternalUrl(): ?string
    {
        return $this->externalUrl;
    }

    /**
     * @param string|null $externalUrl
     * @return DocumentTranslation
     */
    public function setExternalUrl(?string $externalUrl): DocumentTranslation
    {
        $this->externalUrl = $externalUrl;
        return $this;
    }

    /**
     * @return TranslationInterface
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * @param TranslationInterface $translation
     * @return $this
     */
    public function setTranslation(TranslationInterface $translation): DocumentTranslation
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument(): DocumentInterface
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function setDocument(DocumentInterface $document)
    {
        $this->document = $document;
        return $this;
    }
}
