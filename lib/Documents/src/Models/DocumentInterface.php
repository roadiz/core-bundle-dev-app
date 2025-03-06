<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\Common\Collections\Collection;

interface DocumentInterface
{
    public function getFilename(): string;

    /**
     * @return $this
     */
    public function setFilename(string $filename): static;

    public function getMimeType(): ?string;

    /**
     * @return $this
     */
    public function setMimeType(?string $mimeType): static;

    /**
     * Get short type name for current document Mime type.
     */
    public function getShortType(): string;

    /**
     * Get short Mime type.
     */
    public function getShortMimeType(): string;

    /**
     * Is current document an image.
     */
    public function isImage(): bool;

    /**
     * Is current document a vector SVG file.
     */
    public function isSvg(): bool;

    /**
     * Is current document a Webp image.
     */
    public function isWebp(): bool;

    /**
     * Is current document a video.
     */
    public function isVideo(): bool;

    /**
     * Is current document an audio file.
     */
    public function isAudio(): bool;

    /**
     * Is current document a PDF file.
     */
    public function isPdf(): bool;

    public function getFolder(): string;

    /**
     * @return $this
     *
     * @internal You should use DocumentFactory to generate a document folder
     */
    public function setFolder(string $folder): static;

    /**
     * @return string|null Get document relative path : {folder}/{filename}
     */
    public function getRelativePath(): ?string;

    /**
     * @return string|null Get document relative path prefixed with mount information public:// or private://
     */
    public function getMountPath(): ?string;

    /**
     * @return string|null Get document's folder relative path prefixed with mount information public:// or private://
     */
    public function getMountFolderPath(): ?string;

    public function getEmbedId(): ?string;

    /**
     * @return $this
     */
    public function setEmbedId(?string $embedId): static;

    public function getEmbedPlatform(): ?string;

    /**
     * @return $this
     */
    public function setEmbedPlatform(?string $embedPlatform): static;

    /**
     * Tells if current document has embed media information.
     */
    public function isEmbed(): bool;

    public function isPrivate(): bool;

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
     * Is document a raw one.
     */
    public function isRaw(): bool;

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
     * @return Collection<int, FolderInterface>
     */
    public function getFolders(): Collection;

    /**
     * @return $this
     */
    public function addFolder(FolderInterface $folder): static;

    /**
     * @return $this
     */
    public function removeFolder(FolderInterface $folder): static;

    /**
     * Return false if no local file is linked to document. i.e no filename, no folder.
     */
    public function isLocal(): bool;

    /**
     * Return true if current document can be processed by intervention-image (GD, Imagick…).
     */
    public function isProcessable(): bool;

    public function getAlternativeText(): string;

    public function __toString(): string;
}
