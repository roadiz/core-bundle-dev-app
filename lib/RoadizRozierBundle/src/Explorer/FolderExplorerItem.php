<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Explorer;

use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FolderExplorerItem extends AbstractExplorerItem
{
    public function __construct(
        private readonly Folder $folder,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[\Override]
    public function getId(): int
    {
        return $this->folder->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    #[\Override]
    public function getAlternativeDisplayable(): string
    {
        /** @var Folder|null $parent */
        $parent = $this->folder->getParent();
        if (null !== $parent) {
            $translated = $parent->getTranslatedFolders()->first();
            if ($translated) {
                return $translated->getName() ?? '';
            }

            return $parent->getName() ?? '';
        }

        return '';
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return ($this->folder->getTranslatedFolders()->first()) ?
            ($this->folder->getTranslatedFolders()->first()->getName()) :
            ($this->folder->getName() ?? throw new \RuntimeException('Folder name cannot be null'));
    }

    #[\Override]
    public function getOriginal(): Folder
    {
        return $this->folder;
    }

    #[\Override]
    protected function getEditItemPath(): string
    {
        return $this->urlGenerator->generate('foldersEditPage', [
            'folderId' => $this->folder->getId(),
        ]);
    }
}
