<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

interface ModelInterface
{
    /**
     * Return a structured array of data.
     *
     * @return array
     */
    public function toArray(): array;
}
