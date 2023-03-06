<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Utils;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\UnicodeString;

class DownloadedFile extends File
{
    protected ?string $originalFilename;

    /**
     * @return string|null
     */
    public function getOriginalFilename(): ?string
    {
        return $this->originalFilename;
    }

    /**
     * @param string|null $originalFilename
     *
     * @return DownloadedFile
     */
    public function setOriginalFilename(?string $originalFilename): DownloadedFile
    {
        $this->originalFilename = $originalFilename;

        return $this;
    }

    /**
     * Final constructor for safe usage in DownloadedFile::fromUrl
     *
     * @param string $path
     * @param bool   $checkPath
     */
    final public function __construct(string $path, bool $checkPath = true)
    {
        parent::__construct($path, $checkPath);
    }

    /**
     * Transform to lowercase and replace every non-alpha character with an underscore.
     *
     * @param string|null $string
     *
     * @return string
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

    /**
     * @param string      $url
     * @param string|null $originalName
     *
     * @return DownloadedFile|null
     */
    public static function fromUrl(string $url, ?string $originalName = null): ?DownloadedFile
    {
        try {
            $baseName = static::sanitizeFilename(pathinfo($url, PATHINFO_BASENAME));
            $distantHandle = fopen($url, 'r');
            if (false === $distantHandle) {
                return null;
            }
            $original = Utils::streamFor($distantHandle);
            $tmpFile = tempnam(sys_get_temp_dir(), static::sanitizeFilename($baseName));
            if (false === $tmpFile) {
                return null;
            }
            $handle = fopen($tmpFile, 'w');
            $local = Utils::streamFor($handle);
            $local->write($original->getContents());
            $local->close();

            $file = new static($tmpFile);
            if (!empty($originalName)) {
                $file->setOriginalFilename($originalName);
            } else {
                $file->setOriginalFilename($baseName);
            }
            /*
             * Some OEmbed providers won't add any extension in original filename.
             */
            if ($file->getExtension() === '' && null !== $guessedExtension = $file->guessExtension()) {
                $file->setOriginalFilename($file->getOriginalFilename() . '.' . $guessedExtension);
            }

            if ($file->isReadable() && filesize($file->getPathname()) > 0) {
                return $file;
            }
        } catch (RequestException $e) {
            return null;
        } catch (\ErrorException $e) {
            return null;
        }
        return null;
    }
}
