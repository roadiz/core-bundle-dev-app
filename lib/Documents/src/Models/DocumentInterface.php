<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\Common\Collections\Collection;

interface DocumentInterface
{
    public function getFilename(): string;

    /**
     * @param string $filename
     * @return $this
     */
    public function setFilename(string $filename): static;

    /**
     * @return string|null
     */
    public function getMimeType(): ?string;

    /**
     * @param string|null $mimeType
     * @return $this
     */
    public function setMimeType(?string $mimeType): static;

    /**
     * Get short type name for current document Mime type.
     *
     * @return string
     */
    public function getShortType(): string;

    /**
     * Get short Mime type.
     *
     * @return string
     */
    public function getShortMimeType(): string;

    /**
     * Is current document an image.
     *
     * @return bool
     */
    public function isImage(): bool;

    /**
     * Is current document a vector SVG file.
     *
     * @return bool
     */
    public function isSvg(): bool;

    /**
     * Is current document a Webp image.
     *
     * @return bool
     */
    public function isWebp(): bool;

    /**
     * Is current document a video.
     *
     * @return bool
     */
    public function isVideo(): bool;

    /**
     * Is current document an audio file.
     *
     * @return bool
     */
    public function isAudio(): bool;

    /**
     * Is current document a PDF file.
     *
     * @return bool
     */
    public function isPdf(): bool;

    public function getFolder(): string;

    /**
     * @param string $folder
     * @return $this
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
     * @param string|null $embedId
     * @return $this
     */
    public function setEmbedId(?string $embedId): static;

    public function getEmbedPlatform(): ?string;

    /**
     * @param string|null $embedPlatform
     * @return $this
     */
    public function setEmbedPlatform(?string $embedPlatform): static;

    /**
     * Tells if current document has embed media information.
     *
     * @return bool
     */
    public function isEmbed(): bool;

    /**
     * @return bool
     */
    public function isPrivate(): bool;

    /**
     * @param bool $private
     * @return $this
     */
    public function setPrivate(bool $private): static;

    public function getRawDocument(): ?DocumentInterface;

    /**
     * @param DocumentInterface|null $rawDocument the raw document
     * @return $this
     */
    public function setRawDocument(?DocumentInterface $rawDocument = null): static;

    /**
     * Is document a raw one.
     *
     * @return bool
     */
    public function isRaw(): bool;

    /**
     * @param boolean $raw the raw
     * @return $this
     */
    public function setRaw(bool $raw): static;

    /**
     * Gets the downscaledDocument.
     *
     * @return DocumentInterface|null
     */
    public function getDownscaledDocument(): ?DocumentInterface;

    /**
     * @return Collection<int, FolderInterface>
     */
    public function getFolders(): Collection;

    /**
     * @param FolderInterface $folder
     * @return $this
     */
    public function addFolder(FolderInterface $folder): static;

    /**
     * @param FolderInterface $folder
     * @return $this
     */
    public function removeFolder(FolderInterface $folder): static;

    /**
     * Return false if no local file is linked to document. i.e no filename, no folder
     *
     * @return bool
     */
    public function isLocal(): bool;
    /**
     * Return true if current document can be processed by intervention-image (GD, Imagick…).
     *
     * @return bool
     */
    public function isProcessable(): bool;

    /**
     * Gets alternative text for current document.
     *
     * @return string|null Returns null if image is decorative or if no alternative text is set
     */
    public function getAlternativeText(): ?string;

    public function __toString(): string;
}
