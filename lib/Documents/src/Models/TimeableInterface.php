<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface TimeableInterface
{
    public function getMediaDuration(): int;

    /**
     * @return $this
     */
    public function setMediaDuration(int $duration): static;
}
