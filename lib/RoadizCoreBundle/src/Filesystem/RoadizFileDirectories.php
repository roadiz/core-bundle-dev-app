<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Filesystem;

use RZ\Roadiz\Documents\Models\FileAwareInterface;

final readonly class RoadizFileDirectories implements FileAwareInterface
{
    public function __construct(private string $projectDir)
    {
    }

    #[\Override]
    public function getPublicFilesPath(): string
    {
        return $this->projectDir.'/public'.$this->getPublicFilesBasePath();
    }

    #[\Override]
    public function getPublicFilesBasePath(): string
    {
        return '/files';
    }

    #[\Override]
    public function getPrivateFilesPath(): string
    {
        return $this->projectDir.'/var'.$this->getPrivateFilesBasePath();
    }

    #[\Override]
    public function getPrivateFilesBasePath(): string
    {
        return '/files/private';
    }

    #[\Override]
    public function getFontsFilesPath(): string
    {
        return $this->projectDir.'/var'.$this->getFontsFilesBasePath();
    }

    #[\Override]
    public function getFontsFilesBasePath(): string
    {
        return '/files/fonts';
    }

    #[\Override]
    public function getPublicCachePath(): string
    {
        return $this->projectDir.'/public'.$this->getPublicCacheBasePath();
    }

    #[\Override]
    public function getPublicCacheBasePath(): string
    {
        return '/assets';
    }
}
