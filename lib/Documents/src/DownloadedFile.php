<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\UnicodeString;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DownloadedFile extends File
{
    protected ?string $originalFilename = null;

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

        // Remove images double extensions
        // for compatibility with intervention-request
        // and only keep the last one.
        // example: my.image.jpg.webp => my_image_jpg.webp
        $parts = explode('.', $string);
        if (count($parts) > 2) {
            $extension = array_pop($parts);
            // Keep double extension for zip, gz, xz and bz
            if (!\in_array($extension, ['zip', 'gz', 'xz', 'bz', 'bz2', '7z', 'tgz'], true)) {
                $filename = implode('_', $parts);
                $string = $filename.'.'.$extension;
            }
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
            if (!self::isSafeRemoteScheme($url)) {
                return null;
            }

            $baseName = static::sanitizeFilename(pathinfo($url, PATHINFO_BASENAME));
            $client = static::createHttpClient();
            $response = $client->request('GET', $url, [
                'max_redirects' => 3,
                'timeout' => 10,
            ]);
            if (200 !== $response->getStatusCode()) {
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
            try {
                foreach ($client->stream($response) as $chunk) {
                    fwrite($localResource, $chunk->getContent());
                }
                fclose($localResource);
            } catch (\Throwable $exception) {
                fclose($localResource);
                unlink($tmpFile);
                throw $exception;
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
        } catch (\RuntimeException) {
            return null;
        }

        return null;
    }

    /**
     * Any override MUST keep blocking private networks: NoPrivateNetworkHttpClient pins the connection to the
     * address it validated and re-checks every redirect hop, which is what stops a DNS rebind between the
     * check and the fetch.
     */
    protected static function createHttpClient(): HttpClientInterface
    {
        return new NoPrivateNetworkHttpClient(HttpClient::create());
    }

    /**
     * Cheap pre-flight, before any network call. Non-HTTP schemes must be rejected here because Symfony
     * HttpClient throws an InvalidArgumentException — not a RuntimeException — on file:// and php:// URLs.
     *
     * Private-address filtering is deliberately NOT done here: resolving the host up front cannot bind the
     * socket to what it validated (CVE-2026-33486 follow-up). NoPrivateNetworkHttpClient pins the connection
     * to the address it checked and re-checks every redirect hop, so it owns that responsibility.
     */
    private static function isSafeRemoteScheme(string $url): bool
    {
        $parts = parse_url($url);
        if (false === $parts) {
            return false;
        }

        if (!\in_array(strtolower((string) ($parts['scheme'] ?? '')), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        // Short-circuit localhost so we never pay a pointless DNS lookup for a host that can only be blocked.
        return '' !== $host && 'localhost' !== $host && !str_ends_with($host, '.localhost');
    }
}
