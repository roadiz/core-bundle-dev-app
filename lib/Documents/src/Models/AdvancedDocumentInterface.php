<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface AdvancedDocumentInterface extends DocumentInterface, SizeableInterface
{
    /**
     * @return string|null
     */
    public function getImageAverageColor(): ?string;

    /**
     * @param string|null $imageAverageColor
     * @return $this
     */
    public function setImageAverageColor(?string $imageAverageColor): static;

    /**
     * @return int|null
     */
    public function getFilesize(): ?int;

    /**
     * @param int|null $filesize
     * @return $this
     */
    public function setFilesize(?int $filesize): static;
}
