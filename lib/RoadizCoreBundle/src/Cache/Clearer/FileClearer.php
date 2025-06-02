<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

abstract class FileClearer implements ClearerInterface
{
    protected ?string $output = null;

    public function __construct(protected readonly string $cacheDir)
    {
    }

    #[\Override]
    public function clear(): bool
    {
        return false;
    }

    #[\Override]
    public function getOutput(): string
    {
        return $this->output ?? '';
    }

    /**
     * Get global cache directory.
     */
    #[\Override]
    public function getCacheDir(): string
    {
        return $this->cacheDir;
    }
}
