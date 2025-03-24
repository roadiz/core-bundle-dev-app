<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Model;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use RZ\Roadiz\CoreBundle\Entity\Folder;
use RZ\Roadiz\Documents\Models\DocumentTrait;
use Symfony\Component\Serializer\Attribute\Groups;

final class DocumentDto
{
    use DocumentTrait;

    /**
     * @var Collection<Folder>
     */
    public Collection $folders;

    public function __construct(
        #[ApiProperty(identifier: true)]
        private readonly int $id,
        private readonly ?string $filename = null,
        private readonly ?string $mimeType = null,
        private readonly int $imageWidth = 0,
        private readonly int $imageHeight = 0,
        private readonly int $mediaDuration = 0,
        private readonly ?string $imageAverageColor = null,
        private readonly ?string $folder = null,
        private readonly ?string $documentImageCropAlignment = null,
        private readonly ?string $nodeSourceDocumentImageCropAlignment = null,
        private readonly ?string $documentTranslationName = null,
        private readonly ?string $documentTranslationDescription = null,
        private readonly ?string $documentTranslationCopyright = null,
        private readonly ?string $documentTranslationExternalUrl = null,
        ?Collection $folders = null,
    ) {
        $this->folders = new ArrayCollection();
    }

    #[Groups(['document', 'document_display', 'nodes_sources', 'tag', 'attribute'])]
    public function getId(): int
    {
        return $this->id;
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
    public function getDocumentTranslationName(): ?string
    {
        return $this->documentTranslationName;
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationDescription(): ?string
    {
        return $this->documentTranslationDescription;
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationCopyright(): ?string
    {
        return $this->documentTranslationCopyright;
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getDocumentTranslationExternalUrl(): ?string
    {
        return $this->documentTranslationExternalUrl;
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getFilename(): string
    {
        return $this->filename ?? '';
    }

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getAlt(): string
    {
        return !empty($this->getDocumentTranslationName()) ?
            $this->getDocumentTranslationName() :
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

    #[Groups(['document', 'nodes_sources', 'tag', 'attribute'])]
    public function getFolder(): string
    {
        return $this->folder ?? 'documents';
    }

    public function getFolders(): Collection
    {
        return $this->folders;
    }
}
