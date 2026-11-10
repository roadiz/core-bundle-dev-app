<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\MediaFinders;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class FeedResponseSizeCapTest extends TestCase
{
    public function testFeedWithinSizeCapIsReturned(): void
    {
        $client = new MockHttpClient(new MockResponse('{"title":"a small feed"}'));
        $finder = new SimplePodcastFinder($client, 'https://example.com/feed.xml');

        $this->assertSame('{"title":"a small feed"}', $finder->getMediaFeed());
    }

    public function testFeedExceedingSizeCapIsRejected(): void
    {
        $oversized = str_repeat('a', (5 * 1024 * 1024) + 1);
        $client = new MockHttpClient(new MockResponse($oversized));
        $finder = new SimplePodcastFinder($client, 'https://example.com/feed.xml');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Feed response exceeds maximum allowed size.');

        $finder->getMediaFeed();
    }
}
