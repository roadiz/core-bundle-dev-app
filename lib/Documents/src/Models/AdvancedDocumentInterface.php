<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface AdvancedDocumentInterface extends DocumentInterface, SizeableInterface, DisplayableInterface
{
    public function getFilesize(): ?int;

    /**
     * @return $this
     */
    public function setFilesize(?int $filesize): static;
}
