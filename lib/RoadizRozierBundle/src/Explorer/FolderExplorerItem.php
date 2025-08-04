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
    public function getId(): int|string
    {
        return $this->folder->getId() ?? throw new \RuntimeException('Entity must have an ID');
    }

    #[\Override]
    public function getAlternativeDisplayable(): ?string
    {
        /** @var Folder|null $parent */
        $parent = $this->folder->getParent();
        if (null !== $parent) {
            return $parent->getTranslatedFolders()->first() ?
                $parent->getTranslatedFolders()->first()->getName() :
                $parent->getName();
        }

        return '';
    }

    #[\Override]
    public function getDisplayable(): string
    {
        return $this->folder->getTranslatedFolders()->first() ?
            $this->folder->getTranslatedFolders()->first()->getName() :
            $this->folder->getName();
    }

    #[\Override]
    public function getOriginal(): Folder
    {
        return $this->folder;
    }

    #[\Override]
    protected function getEditItemPath(): ?string
    {
        return $this->urlGenerator->generate('foldersEditPage', [
            'folderId' => $this->folder->getId(),
        ]);
    }
}
