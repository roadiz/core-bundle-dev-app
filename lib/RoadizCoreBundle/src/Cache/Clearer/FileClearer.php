<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

abstract class FileClearer implements ClearerInterface
{
    protected ?string $output = null;
    protected string $cacheDir;

    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    public function clear(): bool
    {
        return false;
    }

    public function getOutput(): string
    {
        return $this->output ?? '';
    }

    /**
     * Get global cache directory.
     *
     * @return string
     */
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }
}
