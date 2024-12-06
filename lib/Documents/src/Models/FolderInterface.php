<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\Common\Collections\Collection;

interface FolderInterface
{
    /**
     * @return Collection<int, DocumentInterface>
     */
    public function getDocuments(): Collection;

    /**
     * @return $this
     */
    public function addDocument(DocumentInterface $document): static;

    /**
     * @return $this
     */
    public function removeDocument(DocumentInterface $document): static;

    public function getVisible(): bool;

    public function isVisible(): bool;

    /**
     * @return $this
     */
    public function setVisible(bool $visible): static;

    public function getFolderName(): string;

    public function getName(): ?string;

    /**
     * @return $this
     */
    public function setFolderName(string $folderName): static;

    public function getDirtyFolderName(): string;

    /**
     * @return $this
     */
    public function setDirtyFolderName(string $dirtyFolderName): static;
}
