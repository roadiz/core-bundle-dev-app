<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Repository\FolderTranslationRepository;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Translated representation of Folders.
 *
 * It stores their name.
 */
#[ORM\Entity(repositoryClass: FolderTranslationRepository::class),
    ORM\Table(name: 'folders_translations'),
    ORM\UniqueConstraint(columns: ['folder_id', 'translation_id']),
    UniqueEntity(fields: ['folder', 'translation'])]
class FolderTranslation implements PersistableInterface
{
    use SequentialIdTrait;

    #[ORM\Column(type: 'string', length: 250)]
    #[SymfonySerializer\Groups(['folder', 'document'])]
    #[Assert\Length(max: 250)]
    protected string $name = '';

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Folder::class, inversedBy: 'translatedFolders')]
        #[ORM\JoinColumn(name: 'folder_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[SymfonySerializer\Ignore]
        protected Folder $folder,
        #[ORM\ManyToOne(targetEntity: Translation::class, fetch: 'EXTRA_LAZY', inversedBy: 'folderTranslations')]
        #[ORM\JoinColumn(name: 'translation_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
        #[SymfonySerializer\Groups(['folder', 'document'])]
        protected TranslationInterface $translation,
    ) {
        $this->name = '' != $this->folder->getDirtyFolderName() ? $this->folder->getDirtyFolderName() : $this->folder->getFolderName();
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getFolder(): Folder
    {
        return $this->folder;
    }

    public function setFolder(Folder $folder): FolderTranslation
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Gets the value of translation.
     */
    public function getTranslation(): TranslationInterface
    {
        return $this->translation;
    }

    /**
     * Sets the value of translation.
     *
     * @param TranslationInterface $translation the translation
     */
    public function setTranslation(TranslationInterface $translation): FolderTranslation
    {
        $this->translation = $translation;

        return $this;
    }
}
