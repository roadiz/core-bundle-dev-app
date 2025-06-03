<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

/**
 * Simple FileAwareInterface implementation for tests purposes.
 */
class SimpleFileAware implements FileAwareInterface
{
    public function __construct(private readonly string $basePath)
    {
    }

    #[\Override]
    public function getPublicFilesPath(): string
    {
        return $this->basePath.$this->getPublicFilesBasePath();
    }

    #[\Override]
    public function getPublicFilesBasePath(): string
    {
        return '/files';
    }

    #[\Override]
    public function getPrivateFilesPath(): string
    {
        return $this->basePath.$this->getPrivateFilesBasePath();
    }

    #[\Override]
    public function getPrivateFilesBasePath(): string
    {
        return '/private';
    }

    #[\Override]
    public function getFontsFilesPath(): string
    {
        return $this->basePath.$this->getPrivateFilesBasePath();
    }

    #[\Override]
    public function getFontsFilesBasePath(): string
    {
        return '/fonts';
    }

    #[\Override]
    public function getPublicCachePath(): string
    {
        return '/cache';
    }

    #[\Override]
    public function getPublicCacheBasePath(): string
    {
        return '/cache';
    }
}
