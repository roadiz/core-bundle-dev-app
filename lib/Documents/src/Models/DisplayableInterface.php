<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface DisplayableInterface
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
}
