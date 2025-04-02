<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

/**
 * Base interface for minimal documents information and display.
 */
interface BaseDocumentInterface extends \Stringable
{
    /**
     * @return string|null Get document relative path prefixed with mount information public:// or private://
     */
    public function getMountPath(): ?string;

    public function getFilename(): string;

    public function getMimeType(): ?string;

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
     * @return string|null Get document relative path : {folder}/{filename}
     */
    public function getRelativePath(): ?string;

    public function getEmbedId(): ?string;

    public function getEmbedPlatform(): ?string;

    /**
     * Tells if current document has embed media information.
     */
    public function isEmbed(): bool;

    public function isPrivate(): bool;

    /**
     * Is document a raw one.
     */
    public function isRaw(): bool;

    /**
     * Return false if no local file is linked to document. i.e no filename, no folder.
     */
    public function isLocal(): bool;

    /**
     * Return true if current document can be processed by intervention-image (GD, Imagick…).
     */
    public function isProcessable(): bool;

    public function getAlternativeText(): string;
}
