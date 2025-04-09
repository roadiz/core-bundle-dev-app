<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\Common\Collections\Collection;

interface DocumentInterface extends BaseDocumentInterface
{
    /**
     * @return $this
     */
    public function setFilename(string $filename): static;

    /**
     * @return $this
     */
    public function setMimeType(?string $mimeType): static;

    /**
     * @return $this
     *
     * @internal You should use DocumentFactory to generate a document folder
     */
    public function setFolder(string $folder): static;

    /**
     * @return string|null Get document's folder relative path prefixed with mount information public:// or private://
     */
    public function getMountFolderPath(): ?string;

    /**
     * @return $this
     */
    public function setEmbedId(?string $embedId): static;

    /**
     * @return $this
     */
    public function setEmbedPlatform(?string $embedPlatform): static;

    /**
     * @return $this
     */
    public function setPrivate(bool $private): static;

    public function getRawDocument(): ?DocumentInterface;

    /**
     * @param DocumentInterface|null $rawDocument the raw document
     *
     * @return $this
     */
    public function setRawDocument(?DocumentInterface $rawDocument = null): static;

    /**
     * @param bool $raw the raw
     *
     * @return $this
     */
    public function setRaw(bool $raw): static;

    /**
     * Gets the downscaledDocument.
     */
    public function getDownscaledDocument(): ?DocumentInterface;

    /**
     * @return $this
     */
    public function addFolder(FolderInterface $folder): static;

    /**
     * @return $this
     */
    public function removeFolder(FolderInterface $folder): static;

    /**
     * @return Collection<int, FolderInterface>
     */
    public function getFolders(): Collection;
}
