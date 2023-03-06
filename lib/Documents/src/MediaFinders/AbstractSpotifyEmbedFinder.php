<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractSpotifyEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @var string
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'spotify';
    // https://open.spotify.com/track/6U67bz1ggGoOUllUOvfKFF
    // https://open.spotify.com/embed/track/6U67bz1ggGoOUllUOvfKFF
    protected static string $idPattern = '#^https\:\/\/open\.spotify\.com\/(?<type>track|playlist|artist|album|show|episode)\/(?<id>[a-zA-Z0-9]+)#';
    protected static string $realIdPattern = '#^(?<type>track|playlist|artist|album|show|episode)\/(?<id>[a-zA-Z0-9]+)$#';
    protected ?string $embedUrl;

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://open.spotify.com');
    }

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    /**
     * @inheritDoc
     */
    protected function validateEmbedId(string $embedId = ""): string
    {
        if (preg_match(static::$idPattern, $embedId, $matches) === 1) {
            return $embedId;
        }
        if (preg_match(static::$realIdPattern, $embedId, $matches) === 1) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    /**
     * @inheritDoc
     */
    public function getMediaFeed($search = null)
    {
        if (preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            $url = 'https://open.spotify.com/' . $this->embedId;
        } else {
            $url = $this->embedId;
        }
        $endpoint = "https://embed.spotify.com/oembed";
        $query = [
            'url' => $url,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint . '?' . http_build_query($query));
    }

    /**
     * @inheritDoc
     */
    public function getFeed()
    {
        $feed = parent::getFeed();
        /*
         * We need to extract REAL embedId from oEmbed response, from the HTML field.
         */
        $this->embedUrl = $this->embedId;
        if (preg_match(static::$idPattern, $this->embedId, $matches)) {
            $this->embedId = $matches['type'] . '/' . $matches['id'];
        }

        return $feed;
    }

    /**
     * @inheritDoc
     */
    public function getMediaTitle(): string
    {
        $feed = $this->getFeed();
        return is_array($feed) && isset($feed['title']) ? $feed['title'] : '';
    }

    /**
     * @inheritDoc
     */
    public function getMediaDescription(): string
    {
        $feed = $this->getFeed();
        return is_array($feed) && isset($feed['description']) ? $feed['description'] : '';
    }

    /**
     * @inheritDoc
     */
    public function getMediaCopyright(): string
    {
        $feed = $this->getFeed();
        return is_array($feed) ? $feed['provider_name'] . ' (' . $feed['provider_url'] . ')' : '';
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailURL(): string
    {
        return $this->getFeed()['thumbnail_url'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailName(string $pathinfo): string
    {
        if (preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $ext) === 1) {
            $pathinfo = '.' . $ext['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (preg_match(static::$idPattern, $this->embedId, $matches) === 1) {
            return $matches['type'] . '_' . $matches['id'] . $pathinfo;
        }
        if (preg_match(static::$realIdPattern, $this->embedId, $matches) === 1) {
            return $matches['type'] . '_' . $matches['id'] . $pathinfo;
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

        if (preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            return 'https://open.spotify.com/embed/' . $this->embedId;
        }
        if (preg_match(static::$idPattern, $this->embedId, $matches)) {
            return 'https://open.spotify.com/embed/' . $matches['type'] . '/' . $matches['id'];
        }

        return $this->embedId;
    }
}
