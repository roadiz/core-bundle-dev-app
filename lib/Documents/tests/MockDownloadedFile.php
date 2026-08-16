<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests;

use RZ\Roadiz\Documents\DownloadedFile;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\NoPrivateNetworkHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Feeds fromUrl() canned responses so the private-network guard can be exercised offline.
 *
 * The mock is still wrapped in NoPrivateNetworkHttpClient: the tests assert on the guard, not around it.
 */
final class MockDownloadedFile extends DownloadedFile
{
    /** @var array<MockResponse> */
    public static array $responses = [];

    #[\Override]
    protected static function createHttpClient(): HttpClientInterface
    {
        return new NoPrivateNetworkHttpClient(new MockHttpClient(self::$responses));
    }
}
