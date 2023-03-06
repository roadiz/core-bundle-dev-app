<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

interface ClearerInterface
{
    /**
     * @return bool
     */
    public function clear(): bool;
    /**
     * @return string
     */
    public function getOutput(): string;
    /**
     * Get global cache directory.
     *
     * @return string
     */
    public function getCacheDir(): string;
}
