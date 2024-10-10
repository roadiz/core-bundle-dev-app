<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

/**
 * Soundcloud tools class.
 */
abstract class AbstractSoundcloudEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @var string
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'soundcloud';
    protected static string $idPattern = '#^https\:\/\/soundcloud\.com\/(?<user>[a-z0-9\-]+)\/?#';
    protected static string $realIdPattern = '#^https\:\/\/api\.soundcloud\.com\/(?<type>tracks|playlists|users)\/(?<id>[0-9]+)\/?#';
    protected ?string $embedUrl;

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://api.soundcloud.com') ||
            str_starts_with($embedUrl, 'https://www.soundcloud.com') ||
            str_starts_with($embedUrl, 'https://soundcloud.com');
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
        $endpoint = "https://soundcloud.com/oembed";
        $query = [
            'url' => $this->embedId,
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
        if (!empty($feed['html']) && preg_match('#url\=(?<realId>[a-zA-Z0-9\%\.]+)\&#', $feed['html'], $matches)) {
            $this->embedId = urldecode($matches['realId']);
        }

        return $feed;
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
        return ($this->getFeed()['author_name'] ?? '') . ' (' . ($this->getFeed()['author_url'] ?? '') . ')';
    }

    public function getThumbnailURL(): string
    {
        return $this->getFeed()['thumbnail_url'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailName(string $pathinfo): string
    {
        if (null === $this->embedUrl) {
            $embed = $this->embedId;
        } else {
            $embed = $this->embedUrl;
        }
        if (preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $ext) === 1) {
            $pathinfo = '.' . $ext['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (preg_match(static::$idPattern, $embed, $matches) === 1) {
            return 'soundcloud_' . $matches['user'] . $pathinfo;
        }
        throw new InvalidEmbedId($embed, static::$platform);
    }

    /**
     * Get embed media source URL.
     *
     * ## Available fields
     *
     * * hide_related
     * * show_comments
     * * show_user
     * * show_reposts
     * * visual
     *
     * @param array $options
     *
     * @return string
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        $queryString = [
            'url' => $this->embedId,
        ];

        $queryString['hide_related'] = (int) $options['hide_related'];
        $queryString['show_comments'] = (int) $options['show_comments'];
        $queryString['show_artwork'] = (int) $options['show_artwork'];
        $queryString['show_user'] = (int) $options['show_user'];
        $queryString['show_reposts'] = (int) $options['show_reposts'];
        $queryString['visual'] = (int) $options['visual'];
        if (true === $options['autoplay']) {
            $queryString['auto_play'] = (int) $options['autoplay'];
        }
        $queryString['controls'] = (int) $options['controls'];

        return 'https://w.soundcloud.com/player/?' . http_build_query($queryString);
    }

    protected function areDuplicatesAllowed(): bool
    {
        return true;
    }
}
