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
            if (!self::isSafeRemoteUrl($url)) {
                return null;
            }

            $baseName = static::sanitizeFilename(pathinfo($url, PATHINFO_BASENAME));
            $streamContext = stream_context_create([
                'http' => [
                    'follow_location' => 0,
                    'timeout' => 10,
                ],
                'https' => [
                    'follow_location' => 0,
                    'timeout' => 10,
                ],
            ]);

            $distantResource = fopen($url, 'r', false, $streamContext);
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
            fclose($distantResource);
            fclose($localResource);
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

    private static function isSafeRemoteUrl(string $url): bool
    {
        $parts = parse_url($url);
        if (false === $parts) {
            return false;
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        if (!\in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        if ('' === $host) {
            return false;
        }
        if ('localhost' === $host || str_ends_with($host, '.localhost')) {
            return false;
        }

        $asciiHost = $host;
        if (function_exists('idn_to_ascii')) {
            $idnHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
            if (false !== $idnHost) {
                $asciiHost = strtolower($idnHost);
            }
        }

        if (false !== filter_var($asciiHost, FILTER_VALIDATE_IP)) {
            return self::isGlobalIp($asciiHost);
        }

        $records = dns_get_record($asciiHost, DNS_A + DNS_AAAA);
        if (false === $records || 0 === count($records)) {
            return false;
        }

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;
            if (null === $ip || !self::isGlobalIp($ip)) {
                return false;
            }
        }

        return true;
    }

    private static function isGlobalIp(string $ip): bool
    {
        return false !== filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
