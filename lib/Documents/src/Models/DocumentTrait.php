<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use ApiPlatform\Metadata\ApiProperty;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

trait DocumentTrait
{
    /**
     * Associate mime type to simple types.
     *
     * - code
     * - text
     * - image
     * - word
     * - video
     * - audio
     * - pdf
     * - archive
     * - excel
     * - powerpoint
     * - font
     * - 3d
     *
     * @var array<string, string>
     *
     * @internal
     */
    #[SymfonySerializer\Ignore()]
    protected static array $mimeToIcon = [
        // Code
        'application/javascript' => 'code',
        'application/json' => 'code',
        'application/ld+json' => 'code',
        'text/css' => 'code',
        'text/html' => 'code',
        'text/xml' => 'code',
        // Text
        'text/plain' => 'text',
        // Images types
        'image/avif' => 'image',
        'image/bmp' => 'image',
        'image/gif' => 'image',
        'image/heic' => 'image',
        'image/heif' => 'image',
        'image/jpeg' => 'image',
        'image/png' => 'image',
        'image/svg' => 'image',
        'image/svg+xml' => 'image',
        'image/tiff' => 'image',
        'image/vnd.microsoft.icon' => 'image',
        'image/webp' => 'image',
        'image/x-icon' => 'image',
        // PDF
        'application/pdf' => 'pdf',
        // Audio types
        'audio/aac' => 'audio',
        'audio/ac3' => 'audio',
        'audio/eac3' => 'audio',
        'audio/flac' => 'audio',
        'audio/matroska' => 'audio',
        'audio/mp4' => 'audio',
        'audio/mpeg' => 'audio',
        'audio/ogg' => 'audio',
        'audio/vorbis' => 'audio',
        'audio/wav' => 'audio',
        'audio/webm' => 'audio',
        'audio/x-m4a' => 'audio',
        'audio/x-matroska' => 'audio',
        'audio/x-wav' => 'audio',
        // Video types
        'application/ogg' => 'video',
        'video/3gpp' => 'video',
        'video/3gpp-tt' => 'video',
        'video/3gpp2' => 'video',
        'video/VP8' => 'video',
        'video/matroska' => 'video',
        'video/matroska-3d' => 'video',
        'video/mp4' => 'video',
        'video/mpeg' => 'video',
        'video/ogg' => 'video',
        'video/quicktime' => 'video',
        'video/webm' => 'video',
        'video/x-flv' => 'video',
        'video/x-m4v' => 'video',
        'video/x-matroska' => 'video',
        // Epub type
        'application/epub+zip' => 'epub',
        // Archives types
        'application/gzip' => 'archive',
        'application/x-7z-compressed' => 'archive',
        'application/x-apple-diskimage' => 'archive',
        'application/x-bzip2' => 'archive',
        'application/x-rar-compressed' => 'archive',
        'application/x-tar' => 'archive',
        'application/zip' => 'archive',
        // Office types
        'application/msword' => 'word',
        'application/vnd.ms-excel' => 'excel',
        'application/vnd.ms-office' => 'excel',
        'application/vnd.ms-powerpoint' => 'powerpoint',
        'application/vnd.oasis.opendocument.presentation' => 'powerpoint',
        'application/vnd.oasis.opendocument.spreadsheet' => 'excel',
        'application/vnd.oasis.opendocument.text ' => 'word',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => 'powerpoint',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.template' => 'excel',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'word',
        'text/rtf' => 'word',
        // Fonts types
        'application/font-woff' => 'font',
        'application/font-woff2' => 'font',
        'application/vnd.ms-fontobject' => 'font',
        'application/x-font-opentype' => 'font',
        'application/x-font-truetype' => 'font',
        'application/x-font-ttf' => 'font',
        'font/opentype' => 'font',
        'font/ttf' => 'font',
        'font/woff' => 'font',
        'font/woff2' => 'font',
        // 3d
        'model/gltf+binary' => '3d',
        'model/gltf+json' => '3d',
        'model/gltf-binary' => '3d',
        'model/mtl' => '3d',
        'model/obj' => '3d',
        'model/stl' => '3d',
        'model/u3d' => '3d',
        'model/vnd.gltf+json' => '3d',
        'model/vnd.gltf.binary' => '3d',
        'model/vnd.usda' => '3d',
        'model/vnd.usdz+zip' => '3d',
    ];

    /**
     * @var string[] processable file mime type by GD or Imagick
     *
     * @internal
     */
    #[SymfonySerializer\Ignore()]
    protected static array $processableMimeTypes = [
        'image/png',
        'image/jpeg',
        'image/gif',
        'image/tiff',
        'image/webp',
        'image/avif',
        'image/heic',
        'image/heif',
    ];

    /**
     * Get short type name for current document Mime type.
     */
    #[SymfonySerializer\Ignore()]
    public function getShortType(): string
    {
        if (null !== $this->getMimeType() && isset(static::$mimeToIcon[$this->getMimeType()])) {
            return static::$mimeToIcon[$this->getMimeType()];
        } else {
            return 'unknown';
        }
    }

    /**
     * Get short Mime type.
     */
    #[SymfonySerializer\Ignore()]
    public function getShortMimeType(): string
    {
        if (!empty($this->getMimeType())) {
            $mime = explode('/', $this->getMimeType());

            return $mime[\count($mime) - 1];
        }

        return 'unknown';
    }

    /**
     * Is current document an image.
     */
    #[SymfonySerializer\Ignore()]
    public function isImage(): bool
    {
        return 'image' === static::getShortType();
    }

    /**
     * Is current document a vector SVG file.
     */
    #[SymfonySerializer\Ignore()]
    public function isSvg(): bool
    {
        return 'image/svg+xml' === $this->getMimeType() || 'image/svg' === $this->getMimeType();
    }

    /**
     * Is current document a video.
     */
    #[SymfonySerializer\Ignore()]
    public function isVideo(): bool
    {
        return 'video' === static::getShortType();
    }

    /**
     * Is current document an audio file.
     */
    #[SymfonySerializer\Ignore()]
    public function isAudio(): bool
    {
        return 'audio' === static::getShortType();
    }

    /**
     * Is current document a PDF file.
     */
    #[SymfonySerializer\Ignore()]
    public function isPdf(): bool
    {
        return 'pdf' === static::getShortType();
    }

    #[SymfonySerializer\Ignore()]
    public function isWebp(): bool
    {
        return 'image/webp' === $this->getMimeType();
    }

    #[
        SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute']),
        SymfonySerializer\SerializedName('relativePath'),
    ]
    public function getRelativePath(): ?string
    {
        if ($this->isLocal()) {
            return $this->getFolder().'/'.$this->getFilename();
        } else {
            return null;
        }
    }

    #[
        SymfonySerializer\Groups(['document_mount']),
        SymfonySerializer\SerializedName('mountPath'),
    ]
    public function getMountPath(): ?string
    {
        if (null === $relativePath = $this->getRelativePath()) {
            return null;
        }
        if ($this->isPrivate()) {
            return 'private://'.$relativePath;
        } else {
            return 'public://'.$relativePath;
        }
    }

    #[
        SymfonySerializer\Ignore
    ]
    public function getMountFolderPath(): ?string
    {
        $folder = $this->getFolder();
        if (empty($folder)) {
            return null;
        }
        if ($this->isPrivate()) {
            return 'private://'.$folder;
        } else {
            return 'public://'.$folder;
        }
    }

    /**
     * Tells if current document has embed media information.
     */
    #[SymfonySerializer\Ignore()]
    public function isEmbed(): bool
    {
        return !empty($this->getEmbedId()) && !empty($this->getEmbedPlatform());
    }

    protected function initDocumentTrait(): void
    {
        $this->setFolder(\mb_substr(hash('crc32b', date('YmdHi')), 0, 12));
    }

    #[
        SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute']),
        SymfonySerializer\SerializedName('processable'),
        ApiProperty(
            description: 'Document can be processed as an image for resampling and other image operations.',
            writable: false,
        )
    ]
    public function isProcessable(): bool
    {
        return $this->isImage() && in_array($this->getMimeType(), static::$processableMimeTypes, true);
    }

    #[
        SymfonySerializer\Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute']),
        SymfonySerializer\SerializedName('alt'),
    ]
    public function getAlternativeText(): ?string
    {
        return null;
    }

    /**
     * Return false if no local file is linked to document. i.e no filename, no folder.
     */
    #[SymfonySerializer\Ignore()]
    public function isLocal(): bool
    {
        return '' !== $this->getFilename() && '' !== $this->getFolder();
    }
}
