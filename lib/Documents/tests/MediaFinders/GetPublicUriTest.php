<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\MediaFinders;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

final class GetPublicUriTest extends TestCase
{
    public function testYoutubePublicUriFromRawId(): void
    {
        $finder = new SimpleYoutubeEmbedFinder(HttpClient::create(), 'xxxxxxxxxxx');

        $this->assertSame(
            'https://www.youtube.com/watch?v=xxxxxxxxxxx',
            $finder->getPublicUri()
        );
    }

    public function testYoutubePublicUriFromWatchUrl(): void
    {
        $finder = new SimpleYoutubeEmbedFinder(HttpClient::create(), 'https://www.youtube.com/watch?v=xxxxxxxxxxx');

        $this->assertSame(
            'https://www.youtube.com/watch?v=xxxxxxxxxxx',
            $finder->getPublicUri()
        );
    }

    public function testVimeoPublicUri(): void
    {
        $finder = new SimpleVimeoEmbedFinder(HttpClient::create(), '123456789');

        $this->assertSame(
            'https://vimeo.com/123456789',
            $finder->getPublicUri()
        );
    }

    public function testPodcastFinderHasNoPublicUri(): void
    {
        $finder = new SimplePodcastFinder(HttpClient::create(), 'https://example.com/feed.xml');

        $this->assertNull($finder->getPublicUri());
    }
}
