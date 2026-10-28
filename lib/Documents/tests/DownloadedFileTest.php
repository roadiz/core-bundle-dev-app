<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Documents\DownloadedFile;
use Symfony\Component\HttpClient\Response\MockResponse;

class DownloadedFileTest extends TestCase
{
    /**
     * @dataProvider sanitizeFilenameProvider
     */
    public function testSanitizeFilename(string $input, string $expected): void
    {
        $this->assertEquals($expected, DownloadedFile::sanitizeFilename($input));
    }

    public function sanitizeFilenameProvider(): array
    {
        return [
            [
                'Les-Echos_26022015_Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf',
                'les_echos_26022015_les_entrepreneurs_partent_a_lassaut_du_secteur_bancaire.pdf',
            ],
            [
                'Les-entrepreneurs-partent-à-lassaut-du-secteur-bancaire.pdf',
                'les_entrepreneurs_partent_a_lassaut_du_secteur_bancaire.pdf',
            ],
            [
                'image.jpg',
                'image.jpg',
            ],
            [
                'image with spaces.jpg',
                'image_with_spaces.jpg',
            ],
            [
                'image/with/slashes.jpg',
                'image_with_slashes.jpg',
            ],
            [
                'image.jpg.webp',
                'image_jpg.webp',
            ],
            [
                'image.png.avif',
                'image_png.avif',
            ],
            [
                'image.png.heif',
                'image_png.heif',
            ],
            [
                'folder/folder.image.jpg.webp',
                'folder_folder_image_jpg.webp',
            ],
            [
                'folder/archive.tar.gz',
                'folder_archive.tar.gz',
            ],
            [
                'folder/archive.tar.xz',
                'folder_archive.tar.xz',
            ],
            [
                'folder/archive.tar.zip',
                'folder_archive.tar.zip',
            ],
            [
                'folder/archive.tar.bz',
                'folder_archive.tar.bz',
            ],
            [
                'folder/archive.tar.bz2',
                'folder_archive.tar.bz2',
            ],
            [
                'folder/archive.tar.tgz',
                'folder_archive.tar.tgz',
            ],
            [
                'folder/archive.tar.7z',
                'folder_archive.tar.7z',
            ],
        ];
    }

    /**
     * @dataProvider blockedUrlProvider
     */
    public function testFromUrlRejectsUnsafeUrls(string $url): void
    {
        $this->assertNull(DownloadedFile::fromUrl($url));
    }

    public function blockedUrlProvider(): array
    {
        return [
            ['file:///etc/passwd'],
            ['file:///app/.env'],
            ['file:///app/.env.prod.local'],
            ['php://filter/read=convert.base64-encode/resource=/etc/passwd'],
            ['http://127.0.0.1/test.jpg'],
            ['http://[::1]/test.jpg'],
            ['http://192.168.1.10/test.jpg'],
            ['https://localhost/test.jpg'],
            ['https://sub.localhost/test.jpg'],
            ['ftp://example.com/test.jpg'],
        ];
    }

    /**
     * A DNS rebind answers the guard with a routable address and the connection with a private one.
     * Guard against it by asserting on the address actually connected to, not the one looked up before.
     */
    public function testFromUrlRejectsPrivateAddressAtConnectTime(): void
    {
        // IP literal host: passes the pre-connect check without any DNS lookup, then the socket reports 127.0.0.1.
        MockDownloadedFile::$responses = [new MockResponse('payload', ['primary_ip' => '127.0.0.1'])];

        $this->assertNull(MockDownloadedFile::fromUrl('https://93.184.216.34/test.jpg'));
    }

    public function testFromUrlRejectsRedirectToPrivateAddress(): void
    {
        MockDownloadedFile::$responses = [
            new MockResponse('', [
                'http_code' => 302,
                'primary_ip' => '93.184.216.34',
                'response_headers' => ['Location: http://127.0.0.1/internal.jpg'],
            ]),
            new MockResponse('secret', ['primary_ip' => '127.0.0.1']),
        ];

        $this->assertNull(MockDownloadedFile::fromUrl('https://93.184.216.34/test.jpg'));
    }

    public function testFromUrlDownloadsPublicAddress(): void
    {
        MockDownloadedFile::$responses = [new MockResponse('payload', ['primary_ip' => '93.184.216.34'])];

        $file = MockDownloadedFile::fromUrl('https://93.184.216.34/test.jpg');

        // Without this, the two rejection tests above would pass on a fromUrl() that never downloads anything.
        $this->assertNotNull($file);
        $this->assertSame(7, $file->getSize());
    }
}
