<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface ContextualizedDocumentInterface
{
    public function getDocument(): DocumentInterface;

    /**
     * @return $this
     */
    public function setDocument(DocumentInterface $document): self;

    public function getImageCropAlignment(): ?string;

    /**
     * @return $this
     */
    public function setImageCropAlignment(?string $imageCropAlignment): self;

    public function getHotspot(): ?array;

    /**
     * @return $this
     */
    public function setHotspot(?array $hotspot): self;
}
