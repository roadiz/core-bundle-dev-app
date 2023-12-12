<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface AdvancedDocumentInterface extends DocumentInterface, SizeableInterface, DisplayableInterface
{
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
