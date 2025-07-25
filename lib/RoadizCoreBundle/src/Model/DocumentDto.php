<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use ApiPlatform\Metadata\ApiProperty;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use RZ\Roadiz\Documents\Models\BaseDocumentTrait;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

final class DocumentDto implements BaseDocumentInterface
{
    use BaseDocumentTrait;

    public function __construct(
        #[ApiProperty(identifier: true)]
        private readonly int $id,
        private readonly ?string $filename = null,
        private readonly ?string $mimeType = null,
        private readonly bool $private = false,
        private readonly bool $raw = false,
        private readonly int $imageWidth = 0,
        private readonly int $imageHeight = 0,
        private readonly int $mediaDuration = 0,
        private readonly ?string $embedId = null,
        private readonly ?string $embedPlatform = null,
        private readonly ?string $imageAverageColor = null,
        private readonly ?string $folder = null,
        private readonly ?string $documentImageCropAlignment = null,
        private readonly ?array $documentHotspot = null,
        private readonly ?string $nodeSourceDocumentImageCropAlignment = null,
        private readonly ?array $nodeSourceDocumentHotspot = null,
        private readonly ?string $documentTranslationName = null,
        private readonly ?string $documentTranslationDescription = null,
        private readonly ?string $documentTranslationCopyright = null,
        private readonly ?string $documentTranslationExternalUrl = null,
    ) {
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getId(): int
    {
        return $this->id;
    }

    #[\Override]
    public function getFolder(): string
    {
        return $this->folder ?? 'documents';
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    #[\Override]
    public function getMimeType(): string
    {
        return $this->mimeType ?? 'application/octet-stream';
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getImageWidth(): int
    {
        return $this->imageWidth;
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getImageHeight(): int
    {
        return $this->imageHeight;
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getMediaDuration(): int
    {
        return $this->mediaDuration;
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getImageAverageColor(): string
    {
        return $this->imageAverageColor;
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[\Override]
    public function getFilename(): string
    {
        return $this->filename ?? '';
    }

    #[SerializedName('name')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationName(): ?string
    {
        return !empty($this->documentTranslationName) ?
            $this->documentTranslationName :
            null;
    }

    #[SerializedName('description')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationDescription(): ?string
    {
        return !empty($this->documentTranslationDescription) ?
            $this->documentTranslationDescription :
            null;
    }

    #[SerializedName('copyright')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationCopyright(): ?string
    {
        return !empty($this->documentTranslationCopyright) ?
            $this->documentTranslationCopyright :
            null;
    }

    #[SerializedName('externalUrl')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationExternalUrl(): ?string
    {
        return $this->documentTranslationExternalUrl;
    }

    #[
        Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute']),
        SerializedName('alt'),
        ApiProperty(
            description: 'Document alternative text, for img HTML tag. Returns NULL if image is decorative (alt="").',
            writable: false,
        )
    ]
    #[\Override]
    public function getAlternativeText(): ?string
    {
        return !empty($this->documentTranslationName) ?
            $this->documentTranslationName :
            null;
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    #[\Override]
    public function getRelativePath(): ?string
    {
        if ($this->isLocal()) {
            return $this->getFolder().'/'.$this->getFilename();
        } else {
            return null;
        }
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getImageCropAlignment(): ?string
    {
        if (!empty($this->nodeSourceDocumentImageCropAlignment)) {
            return $this->nodeSourceDocumentImageCropAlignment;
        } else {
            return !empty($this->documentImageCropAlignment) ? $this->documentImageCropAlignment : null;
        }
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getHotspot(): ?array
    {
        if (null !== $this->nodeSourceDocumentHotspot) {
            return $this->nodeSourceDocumentHotspot;
        } else {
            return $this->documentHotspot;
        }
    }

    /*
     * Get image hotspot coordinates as x;y string.
     */
    public function getHotspotAsString(): ?string
    {
        $hotspot = $this->getHotspot();

        if (null !== $hotspot && array_key_exists('areaStartX', $hotspot)) {
            return sprintf(
                '%.5f;%.5f;%.5f;%.5f;%.5f;%.5f',
                $hotspot['x'],
                $hotspot['y'],
                $hotspot['areaStartX'],
                $hotspot['areaStartY'],
                $hotspot['areaEndX'],
                $hotspot['areaEndY'],
            );
        }

        return null !== $hotspot ? sprintf(
            '%.5f;%.5f',
            $hotspot['x'],
            $hotspot['y']
        ) : null;
    }

    #[\Override]
    public function __toString(): string
    {
        if (!empty($this->getFilename())) {
            return $this->getFilename();
        }
        if (!empty($this->documentTranslationName)) {
            return $this->documentTranslationName;
        }
        if (!empty($this->getEmbedPlatform())) {
            return $this->getEmbedPlatform().' ('.$this->getEmbedId().')';
        }

        return (string) $this->getId();
    }

    #[\Override]
    public function compareTo($other): int
    {
        if (!$other instanceof DocumentDto) {
            throw new \InvalidArgumentException('Can only compare to same class instances.');
        }

        return $this->getId() <=> $other->getId();
    }
}
