<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface SizeableInterface
{
    public function getImageWidth(): int;

    /**
     * @return $this
     */
    public function setImageWidth(int $imageWidth): static;

    public function getImageHeight(): int;

    /**
     * @return $this
     */
    public function setImageHeight(int $imageHeight): static;

    public function getImageRatio(): ?float;

    public function getHotspot(): ?array;

    public function setHotspot(?array $hotspot): static;

    public function getHotspotAsString(): ?string;

    public function getImageCropAlignment(): ?string;
}
