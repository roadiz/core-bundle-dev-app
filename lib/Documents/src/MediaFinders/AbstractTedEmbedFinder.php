<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractTedEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @var string
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'ted';
    protected static string $idPattern = '#^https\:\/\/(www\.)?ted\.com\/talks\/(?<id>[a-zA-Z0-9\-\_]+)#';

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://www.ted.com/talks');
    }

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    protected function validateEmbedId(string $embedId = ""): string
    {
        if (preg_match(static::$idPattern, $embedId, $matches)) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    public function getMediaFeed($search = null)
    {
        $endpoint = "https://www.ted.com/services/v1/oembed.json";
        $query = [
            'url' => $this->embedId,
        ];

        return $this->downloadFeedFromAPI($endpoint . '?' . http_build_query($query));
    }

    public function getMediaTitle(): string
    {
        return $this->getFeed()['title'] ?? '';
    }

    public function getMediaDescription(): string
    {
        return $this->getFeed()['description'] ?? '';
    }

    public function getMediaCopyright(): string
    {
        return ($this->getFeed()['author_name'] ?? '') . ' - ' . ($this->getFeed()['provider_name'] ?? '') . ' (' . ($this->getFeed()['author_url'] ?? '') . ')';
    }

    public function getThumbnailURL(): string
    {
        return $this->getFeed()['thumbnail_url'] ?? '';
    }

    public function getThumbnailName(string $pathinfo): string
    {
        if (preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $ext) === 1) {
            $pathinfo = '.' . $ext['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (preg_match(static::$idPattern, $this->embedId, $matches) === 1) {
            return 'ted_talk_' . $matches['id'] . $pathinfo;
        }
        throw new InvalidEmbedId($this->embedId, static::$platform);
    }

    /**
     * Get embed media source URL.
     *
     * @param array $options
     *
     * @return string
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        if (preg_match(static::$idPattern, $this->embedId, $matches)) {
            return 'https://embed.ted.com/talks/' . $matches['id'];
        }

        return $this->embedId;
    }
}
