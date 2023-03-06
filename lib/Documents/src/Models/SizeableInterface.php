<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface SizeableInterface
{
    /**
     * @return int
     */
    public function getImageWidth(): int;

    /**
     * @param int $imageWidth
     * @return $this
     */
    public function setImageWidth(int $imageWidth): static;

    /**
     * @return int
     */
    public function getImageHeight(): int;

    /**
     * @param int $imageHeight
     * @return $this
     */
    public function setImageHeight(int $imageHeight): static;

    /**
     * @return float|null
     */
    public function getImageRatio(): ?float;
}
