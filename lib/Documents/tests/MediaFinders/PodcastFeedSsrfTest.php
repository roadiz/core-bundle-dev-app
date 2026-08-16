<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\MediaFinders;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * The podcast feed URL is supplied by the user, so getMediaFeed() must not be able to reach the private network.
 *
 * Redirect-hop validation is not covered here: MockHttpClient does not drive the AsyncResponse redirect
 * chain that NoPrivateNetworkHttpClient uses, so a mocked 3xx never reaches the per-hop check.
 */
class PodcastFeedSsrfTest extends TestCase
{
    public function testGetMediaFeedRejectsPrivateAddressAtConnectTime(): void
    {
        // IP literal host: passes the pre-connect check without any DNS lookup, then the socket reports 127.0.0.1.
        MockPodcastFinder::$responses = [new MockResponse('<rss/>', ['primary_ip' => '127.0.0.1'])];
        $finder = new MockPodcastFinder('https://93.184.216.34/feed.xml', false);

        $this->expectException(TransportExceptionInterface::class);
        $finder->getMediaFeed();
    }

    public function testGetMediaFeedReadsPublicAddress(): void
    {
        // Without this, the two rejection tests above would pass on a getMediaFeed() that never fetches anything.
        MockPodcastFinder::$responses = [new MockResponse('<rss/>', ['primary_ip' => '93.184.216.34'])];
        $finder = new MockPodcastFinder('https://93.184.216.34/feed.xml', false);

        $this->assertSame('<rss/>', (string) $finder->getMediaFeed());
    }
}
