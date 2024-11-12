<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface DisplayableInterface
{
    public function getImageAverageColor(): ?string;

    /**
     * @return $this
     */
    public function setImageAverageColor(?string $imageAverageColor): static;
}
