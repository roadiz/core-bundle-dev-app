<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\TagTranslationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translated representation of Tags.
 *
 * It stores their name and description.
 */
#[ORM\Entity(repositoryClass: TagTranslationRepository::class),
    ORM\Table(name: 'tags_translations'),
    ORM\UniqueConstraint(columns: ['tag_id', 'translation_id']),
    Gedmo\Loggable(logEntryClass: UserLogEntry::class),
    UniqueEntity(fields: ['tag', 'translation'])]
class TagTranslation implements PersistableInterface
{
    use SequentialIdTrait;

    #[ORM\Column(type: 'string', length: 250)]
    #[SymfonySerializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    #[Gedmo\Versioned]
    protected string $name = '';

    #[ORM\Column(type: 'text', nullable: true)]
    #[SymfonySerializer\Groups(['tag', 'node', 'nodes_sources'])]
    #[Gedmo\Versioned]
    protected ?string $description = null;

    /**
     * @var Collection<int, TagTranslationDocuments>
     */
    #[ORM\OneToMany(
        mappedBy: 'tagTranslation',
        targetEntity: TagTranslationDocuments::class,
        cascade: ['persist', 'merge'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    #[SymfonySerializer\Ignore]
    protected Collection $tagTranslationDocuments;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Tag::class, inversedBy: 'translatedTags')]
        #[ORM\JoinColumn(name: 'tag_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[SymfonySerializer\Ignore]
        protected Tag $tag,
        #[ORM\ManyToOne(targetEntity: Translation::class, fetch: 'EXTRA_LAZY', inversedBy: 'tagTranslations')]
        #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[SymfonySerializer\Groups(['tag', 'node', 'nodes_sources'])]
        protected TranslationInterface $translation,
    ) {
        $this->tagTranslationDocuments = new ArrayCollection();
        $this->name = '' != $this->tag->getDirtyTagName() ? $this->tag->getDirtyTagName() : $this->tag->getTagName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): TagTranslation
    {
        $this->name = $name ?? '';

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): TagTranslation
    {
        $this->description = $description;

        return $this;
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function setTag(Tag $tag): TagTranslation
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    public function setTranslation(TranslationInterface $translation): TagTranslation
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * After clone method.
     *
     * Be careful not to persist nor flush current entity after
     * calling clone as it empties its relations.
     */
    public function __clone()
    {
        if ($this->id) {
            $this->id = null;
            $documents = $this->getDocuments();
            $this->tagTranslationDocuments = new ArrayCollection();
            /** @var TagTranslationDocuments $document */
            foreach ($documents as $document) {
                $cloneDocument = clone $document;
                $this->tagTranslationDocuments->add($cloneDocument);
                $cloneDocument->setTagTranslation($this);
            }
        }
    }

    #[SymfonySerializer\Groups(['tag'])]
    public function getDocuments(): array
    {
        return array_map(fn (TagTranslationDocuments $tagTranslationDocument) => $tagTranslationDocument->getDocument(), $this->getTagTranslationDocuments()->toArray());
    }

    public function getTagTranslationDocuments(): Collection
    {
        return $this->tagTranslationDocuments;
    }

    public function setTagTranslationDocuments(Collection $tagTranslationDocuments): TagTranslation
    {
        $this->tagTranslationDocuments = $tagTranslationDocuments;

        return $this;
    }
}
