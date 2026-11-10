<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Tests\MediaFinders;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;
use Symfony\Component\HttpClient\HttpClient;

final class ApplePodcastEmbedFinderTest extends TestCase
{
    public function testValidEmbedIdIsAccepted(): void
    {
        $finder = new SimpleApplePodcastEmbedFinder(
            HttpClient::create(),
            'https://podcasts.apple.com/us/podcast/some-show/id123456789'
        );

        $this->assertSame(
            'https://podcasts.apple.com/us/podcast/some-show/id123456789',
            $finder->getEmbedId()
        );
    }

    public function testInvalidEmbedIdIsRejected(): void
    {
        $this->expectException(InvalidEmbedId::class);

        new SimpleApplePodcastEmbedFinder(HttpClient::create(), 'not-a-valid-apple-podcast-url');
    }

    public function testGetSourceRejectsInvalidEmbedIdRatherThanAlwaysMatching(): void
    {
        $finder = new SimpleApplePodcastEmbedFinder(
            HttpClient::create(),
            'https://podcasts.apple.com/us/podcast/some-show/id123456789'
        );
        $options = [];

        $this->assertSame(
            'https://embed.podcasts.apple.com/us/podcast/some-show/id123456789',
            $finder->getSource($options)
        );
    }
}
