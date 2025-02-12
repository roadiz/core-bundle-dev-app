<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

/**
 * Vimeo tools class.
 */
abstract class AbstractVimeoEmbedFinder extends AbstractEmbedFinder
{
    protected static string $realIdPattern = '#(?<id>[0-9]+)$#';
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'vimeo';

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://vimeo.com/')
            || str_starts_with($embedUrl, 'https://www.vimeo.com/');
    }

    protected function validateEmbedId(string $embedId = ''): string
    {
        if (1 === preg_match('#(?<id>[0-9]+)$#', $embedId, $matches)) {
            return $matches['id'];
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    /**
     * @return bool Allow video without thumbnails
     */
    public function isEmptyThumbnailAllowed(): bool
    {
        return true;
    }

    /**
     * Tell if embed media exists after its API feed.
     */
    public function exists(): bool
    {
        $feed = $this->getFeed();

        return is_array($feed) && isset($feed['video_id']);
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
        return $this->getFeed()['author_name'] ?? '';
    }

    public function getThumbnailURL(): string
    {
        return $this->getFeed()['thumbnail_url'] ?? '';
    }

    public function getMediaWidth(): ?int
    {
        return $this->getFeed()['width'] ?? null;
    }

    public function getMediaHeight(): ?int
    {
        return $this->getFeed()['height'] ?? null;
    }

    public function getMediaDuration(): ?int
    {
        return $this->getFeed()['duration'] ?? null;
    }

    public function getSearchFeed(string $searchTerm, ?string $author = null, int $maxResults = 15): ?string
    {
        return null;
    }

    public function getMediaFeed(?string $search = null): string
    {
        if (preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            $url = 'https://vimeo.com/video/'.$this->embedId;
        } else {
            $url = $this->embedId;
        }
        $endpoint = 'https://vimeo.com/api/oembed.json';
        $query = [
            'url' => $url,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint.'?'.http_build_query($query));
    }

    /**
     * Get embed media source URL.
     *
     * ### Vimeo additional embed parameters
     *
     * * displayTitle
     * * byline
     * * portrait
     * * color
     * * api
     * * muted
     * * autopause
     * * automute
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        $queryString = [];

        $queryString['title'] = (int) $options['displayTitle'];
        $queryString['byline'] = (int) $options['byline'];
        $queryString['portrait'] = (int) $options['portrait'];
        $queryString['api'] = (int) $options['api'];
        $queryString['loop'] = (int) $options['loop'];
        $queryString['fullscreen'] = (int) $options['fullscreen'];
        $queryString['controls'] = (int) $options['controls'];
        $queryString['autopause'] = (int) $options['autopause'];
        $queryString['automute'] = (int) $options['automute'];

        if (null !== $options['color']) {
            $queryString['color'] = $options['color'];
        }
        if (null !== $options['id']) {
            $queryString['player_id'] = $options['id'];
        }
        if (null !== $options['identifier']) {
            $queryString['player_id'] = $options['identifier'];
        }
        if ($options['autoplay']) {
            $queryString['autoplay'] = (int) $options['autoplay'];
            $queryString['playsinline'] = (int) $options['autoplay'];
        }
        if (null !== $options['background']) {
            $queryString['background'] = (int) $options['background'];
        }
        if ($options['muted']) {
            $queryString['muted'] = (int) $options['muted'];
        }

        return 'https://player.vimeo.com/video/'.$this->embedId.'?'.http_build_query($queryString);
    }
}
