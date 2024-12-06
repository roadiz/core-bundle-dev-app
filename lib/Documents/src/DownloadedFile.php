<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\UnicodeString;

class DownloadedFile extends File
{
    protected ?string $originalFilename;

    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    public function setOriginalFilename(?string $originalFilename): DownloadedFile
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * Final constructor for safe usage in DownloadedFile::fromUrl.
     */
    final public function __construct(string $path, bool $checkPath = true)
    {
        parent::__construct($path, $checkPath);
    }

    /**
     * Transform to lowercase and replace every non-alpha character with an underscore.
     */
    public static function sanitizeFilename(?string $string): string
    {
        if (null === $string) {
            return '';
        }

        return (new UnicodeString($string))
            ->ascii()
            ->trim()
            ->replaceMatches('#([^a-zA-Z0-9\.]+)#', '_')
            ->lower()
            ->toString()
        ;
    }

    public static function fromUrl(string $url, ?string $originalName = null): ?DownloadedFile
    {
        try {
            $baseName = static::sanitizeFilename(pathinfo($url, PATHINFO_BASENAME));
            $distantResource = fopen($url, 'r');
            if (false === $distantResource) {
                return null;
            }

            $tmpFile = tempnam(sys_get_temp_dir(), static::sanitizeFilename($baseName));
            if (false === $tmpFile) {
                return null;
            }
            $localResource = fopen($tmpFile, 'w');
            if (false === $localResource) {
                throw new \RuntimeException('Unable to open local resource.');
            }
            $result = \stream_copy_to_stream($distantResource, $localResource);
            if (false === $result) {
                throw new \RuntimeException('Unable to copy distant stream to local resource.');
            }

            $file = new static($tmpFile);
            if (!empty($originalName)) {
                $file->setOriginalFilename($originalName);
            } else {
                $file->setOriginalFilename($baseName);
            }
            /*
             * Some OEmbed providers won't add any extension in original filename.
             */
            if ('' === $file->getExtension() && null !== $guessedExtension = $file->guessExtension()) {
                $file->setOriginalFilename($file->getOriginalFilename().'.'.$guessedExtension);
            }

            if ($file->isReadable() && filesize($file->getPathname()) > 0) {
                return $file;
            }
        } catch (\RuntimeException $e) {
            return null;
        }

        return null;
    }
}
