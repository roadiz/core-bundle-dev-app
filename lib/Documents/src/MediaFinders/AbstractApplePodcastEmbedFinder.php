<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractApplePodcastEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'apple_podcast';
    protected static string $idPattern = '#^https\:\/\/(?:podcasts\.apple\.com|embed\.podcasts\.apple\.com)\/(?<path>[a-zA-Z\-]+/podcast/[a-zA-Z0-9\%\-\/\=?]+)\/?#';

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://podcasts.apple.com')
            || str_starts_with($embedUrl, 'https://embed.podcasts.apple.com');
    }

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    protected function validateEmbedId(string $embedId = ''): string
    {
        if (false !== preg_match(static::$idPattern, $embedId, $matches)) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    public function getMediaFeed(?string $search = null): string
    {
        return '';
    }

    public function getFeed(): array|\SimpleXMLElement|null
    {
        if (null === $this->feed) {
            $feed = $this->downloadFeedFromAPI($this->embedId);

            /*
             * Try to extract JsonLD micro data from podcast webpage
             */
            if (preg_match('#<script id="?schema\:(?:episode|show)"? type="application\/ld\+json">([\S\s\n]*?)<\/script>#', $feed, $matches)) {
                $feed = json_decode($matches[1], true);

                if (isset($feed['@type']) && ('PodcastEpisode' === $feed['@type'] || 'CreativeWorkSeries' === $feed['@type'])) {
                    $this->feed = $feed;

                    return $feed;
                }
            }
        }

        return $this->feed;
    }

    public function getMediaTitle(): ?string
    {
        return $this->getFeed()['name'] ?? null;
    }

    public function getMediaDescription(): ?string
    {
        return $this->getFeed()['description'] ?? null;
    }

    public function getMediaCopyright(): ?string
    {
        return $this->getFeed()['productionCompany'] ?? null;
    }

    public function getThumbnailURL(): ?string
    {
        return $this->getFeed()['thumbnailUrl'] ?? null;
    }

    public function getThumbnailName(string $pathinfo): string
    {
        return $pathinfo;
    }

    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        if (false !== preg_match(static::$idPattern, $this->embedId, $matches)) {
            return 'https://embed.podcasts.apple.com/'.$matches['path'];
        }

        return $this->embedId;
    }

    public function getShortType(): string
    {
        return 'podcast';
    }

    protected function areDuplicatesAllowed(): bool
    {
        return true;
    }

    public function isEmptyThumbnailAllowed(): bool
    {
        return true;
    }
}
