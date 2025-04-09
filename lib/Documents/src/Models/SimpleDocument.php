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

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function setFolder(string $folder): static
    {
        $this->folder = $folder;

        return $this;
    }

    public function getEmbedId(): ?string
    {
        return $this->embedId;
    }

    public function setEmbedId(?string $embedId): static
    {
        $this->embedId = $embedId;

        return $this;
    }

    public function getEmbedPlatform(): ?string
    {
        return $this->embedPlatform;
    }

    public function setEmbedPlatform(?string $embedPlatform): static
    {
        $this->embedPlatform = $embedPlatform;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    public function getRawDocument(): ?DocumentInterface
    {
        return $this->rawDocument;
    }

    public function setRawDocument(?DocumentInterface $rawDocument = null): static
    {
        $this->rawDocument = $rawDocument;

        return $this;
    }

    public function isRaw(): bool
    {
        return $this->raw;
    }

    public function setRaw(bool $raw): static
    {
        $this->raw = $raw;

        return $this;
    }

    public function getDownscaledDocument(): ?DocumentInterface
    {
        return $this->downscaledDocument;
    }

    public function setDownscaledDocument(?DocumentInterface $downscaledDocument): static
    {
        $this->downscaledDocument = $downscaledDocument;

        return $this;
    }

    public function getFolders(): Collection
    {
        return $this->folders;
    }

    public function setFolders(Collection $folders): static
    {
        $this->folders = $folders;

        return $this;
    }

    public function addFolder(FolderInterface $folder): static
    {
        $this->folders->add($folder);

        return $this;
    }

    public function removeFolder(FolderInterface $folder): static
    {
        $this->folders->removeElement($folder);

        return $this;
    }

    public function __toString(): string
    {
        return $this->getFilename();
    }
}
