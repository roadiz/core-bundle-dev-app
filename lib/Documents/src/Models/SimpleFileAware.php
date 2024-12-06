<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Models;

/**
 * Simple FileAwareInterface implementation for tests purposes.
 */
class SimpleFileAware implements FileAwareInterface
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
    }

    public function getPublicFilesPath(): string
    {
        return $this->basePath.$this->getPublicFilesBasePath();
    }

    public function getPublicFilesBasePath(): string
    {
        return '/files';
    }

    public function getPrivateFilesPath(): string
    {
        return $this->basePath.$this->getPrivateFilesBasePath();
    }

    public function getPrivateFilesBasePath(): string
    {
        return '/private';
    }

    public function getFontsFilesPath(): string
    {
        return $this->basePath.$this->getPrivateFilesBasePath();
    }

    public function getFontsFilesBasePath(): string
    {
        return '/fonts';
    }

    public function getPublicCachePath(): string
    {
        return '/cache';
    }

    public function getPublicCacheBasePath(): string
    {
        return '/cache';
    }
}
