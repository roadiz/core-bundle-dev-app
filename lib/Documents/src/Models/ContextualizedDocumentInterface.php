<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface ContextualizedDocumentInterface
{
    public function getDocument(): DocumentInterface;

    /**
     * @return $this
     */
    public function setDocument(DocumentInterface $document): static;

    public function getImageCropAlignment(): ?string;

    /**
     * @return $this
     */
    public function setImageCropAlignment(?string $imageCropAlignment): static;

    public function getHotspot(): ?array;

    /**
     * @return $this
     */
    public function setHotspot(?array $hotspot): static;
}
