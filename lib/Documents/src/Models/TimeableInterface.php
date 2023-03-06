<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

interface TimeableInterface
{
    /**
     * @return int
     */
    public function getMediaDuration(): int;

    /**
     * @param int $duration
     * @return $this
     */
    public function setMediaDuration(int $duration): static;
}
