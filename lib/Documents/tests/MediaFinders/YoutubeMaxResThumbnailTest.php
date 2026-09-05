<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\MediaFinders;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

final class YoutubeMaxResThumbnailTest extends TestCase
{
    public function testHqDefaultBecomesMaxRes(): void
    {
        $finder = new StubbedThumbnailYoutubeEmbedFinder(
            HttpClient::create(),
            'IFANWojM8LU',
            'https://i.ytimg.com/vi/IFANWojM8LU/hqdefault.jpg'
        );

        $this->assertSame(
            'https://i.ytimg.com/vi/IFANWojM8LU/maxresdefault.jpg',
            $finder->exposeMaxResThumbnailURL()
        );
    }

    public function testUnknownThumbnailUrlYieldsNull(): void
    {
        $finder = new StubbedThumbnailYoutubeEmbedFinder(
            HttpClient::create(),
            'IFANWojM8LU',
            'https://example.com/cover.png'
        );

        $this->assertNull($finder->exposeMaxResThumbnailURL());
    }

    public function testEmptyThumbnailUrlYieldsNull(): void
    {
        $finder = new StubbedThumbnailYoutubeEmbedFinder(HttpClient::create(), 'IFANWojM8LU', '');

        $this->assertNull($finder->exposeMaxResThumbnailURL());
    }
}
