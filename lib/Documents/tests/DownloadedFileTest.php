<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Documents\DownloadedFile;
use Symfony\Component\HttpClient\Response\MockResponse;

class DownloadedFileTest extends TestCase
{
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

    public function testFromUrlDownloadsPublicAddress(): void
    {
        MockDownloadedFile::$responses = [new MockResponse('payload', ['primary_ip' => '93.184.216.34'])];

        $file = MockDownloadedFile::fromUrl('https://93.184.216.34/test.jpg');

        // Without this, the two rejection tests above would pass on a fromUrl() that never downloads anything.
        $this->assertNotNull($file);
        $this->assertSame(7, $file->getSize());
    }
}
