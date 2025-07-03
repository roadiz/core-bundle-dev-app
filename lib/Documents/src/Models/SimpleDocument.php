<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * Simple document implementation for tests purposes.
 */
class SimpleDocument implements DocumentInterface
{
    use BaseDocumentTrait;
    use DocumentTrait;

    private string $filename = '';
    private string $folder = '';
    private ?string $embedId = null;
    private ?string $alternativeText = null;
    private ?string $embedPlatform = null;
    private ?string $mimeType = null;
    private bool $private = false;
    private bool $raw = false;
    private Collection $folders;
    private ?DocumentInterface $rawDocument = null;
    private ?DocumentInterface $downscaledDocument = null;

    public function __construct()
    {
        $this->initDocumentTrait();
        $this->folders = new ArrayCollection();
    }

    #[\Override]
    public function getFilename(): string
    {
        return $this->filename;
    }

    #[\Override]
    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    #[\Override]
    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    #[\Override]
    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    #[\Override]
    public function getFolder(): string
    {
        return $this->folder;
    }

    #[\Override]
    public function setFolder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    #[\Override]
    public function getEmbedId(): ?string
    {
        return $this->embedId;
    }

    #[\Override]
    public function setEmbedId(?string $embedId): static
    {
        $this->embedId = $embedId;

        return $this;
    }

    #[\Override]
    public function getEmbedPlatform(): ?string
    {
        return $this->embedPlatform;
    }

    #[\Override]
    public function setEmbedPlatform(?string $embedPlatform): static
    {
        $this->embedPlatform = $embedPlatform;

        return $this;
    }

    #[\Override]
    public function isPrivate(): bool
    {
        return $this->private;
    }

    #[\Override]
    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    #[\Override]
    public function getRawDocument(): ?DocumentInterface
    {
        return $this->rawDocument;
    }

    #[\Override]
    public function setRawDocument(?DocumentInterface $rawDocument = null): static
    {
        $this->rawDocument = $rawDocument;

        return $this;
    }

    #[\Override]
    public function isRaw(): bool
    {
        return $this->raw;
    }

    #[\Override]
    public function setRaw(bool $raw): static
    {
        $this->raw = $raw;

        return $this;
    }

    #[\Override]
    public function getDownscaledDocument(): ?DocumentInterface
    {
        return $this->downscaledDocument;
    }

    public function setDownscaledDocument(?DocumentInterface $downscaledDocument): static
    {
        $this->downscaledDocument = $downscaledDocument;

        return $this;
    }

    #[\Override]
    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function setFolders(Collection $folders): static
    {
        $this->folders = $folders;

        return $this;
    }

    #[\Override]
    public function addFolder(FolderInterface $folder): static
    {
        $this->folders->add($folder);

        return $this;
    }

    #[\Override]
    public function removeFolder(FolderInterface $folder): static
    {
        $this->folders->removeElement($folder);

        return $this;
    }

    public function setAlternativeText(?string $alternativeText): static
    {
        $this->alternativeText = $alternativeText;

        return $this;
    }

    #[\Override]
    public function getAlternativeText(): ?string
    {
        return $this->alternativeText;
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getFilename();
    }

    #[\Override]
    public function compareTo($other)
    {
        if (!$other instanceof DocumentInterface) {
            throw new \InvalidArgumentException('Can only compare to DocumentInterface instances.');
        }

        return $this->getFilename() === $other->getFilename()
            && $this->getFolder() === $other->getFolder() ? 0 : -1;
    }
}
