<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\Common\Collections\Collection;

interface FolderInterface
{
    /**
     * @return Collection<DocumentInterface>
     */
    public function getDocuments(): Collection;

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function addDocument(DocumentInterface $document): static;

    /**
     * @param DocumentInterface $document
     * @return $this
     */
    public function removeDocument(DocumentInterface $document): static;

    /**
     * @return bool
     */
    public function getVisible(): bool;

    /**
     * @param bool $visible
     * @return $this
     */
    public function setVisible(bool $visible): static;

    /**
     * @return string
     */
    public function getFolderName(): string;

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string $folderName
     * @return $this
     */
    public function setFolderName(string $folderName): static;

    /**
     * @return string
     */
    public function getDirtyFolderName(): string;

    /**
     * @param  string $dirtyFolderName
     * @return $this
     */
    public function setDirtyFolderName(string $dirtyFolderName): static;
}
