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

    public function getFolder(): string
    {
        return $this->folder ?? 'documents';
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getMimeType(): string
    {
        return $this->mimeType;
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
    public function getFilename(): string
    {
        return $this->filename ?? '';
    }

    #[SerializedName('name')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationName(): ?string
    {
        return $this->documentTranslationName;
    }

    #[SerializedName('description')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationDescription(): ?string
    {
        return $this->documentTranslationDescription;
    }

    #[SerializedName('copyright')]
    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationCopyright(): ?string
    {
        return $this->documentTranslationCopyright;
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
            description: 'Document alternative text, for img HTML tag.',
            writable: false,
        )
    ]
    public function getAlternativeText(): string
    {
        return !empty($this->documentTranslationName) ?
            $this->documentTranslationName :
            $this->getFilename();
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
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
        if (null !== $this->nodeSourceDocumentImageCropAlignment) {
            return $this->nodeSourceDocumentImageCropAlignment;
        } else {
            return $this->documentImageCropAlignment;
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

        return null !== $hotspot ? sprintf(
            '%.5f;%.5f',
            $hotspot['x'],
            $hotspot['y']
        ) : null;
    }

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
}
