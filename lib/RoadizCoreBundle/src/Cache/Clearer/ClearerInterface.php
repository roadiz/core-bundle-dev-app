<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

interface ClearerInterface
{
    public function clear(): bool;

    public function getOutput(): string;

    /**
     * Get global cache directory.
     */
    public function getCacheDir(): string;
}
