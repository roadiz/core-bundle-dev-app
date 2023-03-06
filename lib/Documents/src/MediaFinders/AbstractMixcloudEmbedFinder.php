<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractMixcloudEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @var string
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'mixcloud';
    protected static string $idPattern = '#^https\:\/\/www\.mixcloud\.com\/(?<author>[a-zA-Z0-9\-]+)\/(?<id>[a-zA-Z0-9\-]+)\/?$#';

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://www.mixcloud.com');
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
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    /**
     * @inheritDoc
     */
    public function getMediaFeed($search = null)
    {
        $endpoint = "https://www.mixcloud.com/oembed/";
        $query = [
            'url' => $this->embedId,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint . '?' . http_build_query($query));
    }

    /**
     * @inheritDoc
     */
    public function getMediaTitle(): string
    {
        return $this->getFeed()['title'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getMediaDescription(): string
    {
        return $this->getFeed()['description'] ?? '';
    }

    /**
     * @inheritDoc
     */
    public function getMediaCopyright(): string
    {
        return ($this->getFeed()['author_name'] ?? '') . ' (' . ($this->getFeed()['author_url'] ?? '') . ')';
    }

    /**
     * @inheritDoc
     */
    public function getThumbnailURL(): string
    {
        return $this->getFeed()['image'] ?? '';
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
            return $matches['author'] . '_' . $matches['id'] . $pathinfo;
        }
        throw new InvalidEmbedId($this->embedId, static::$platform);
    }

    /**
     * Get embed media source URL.
     *
     * ### Mixcloud additional embed parameters
     *
     * * start
     * * end
     * * mini
     * * hide_cover
     *
     * @param array $options
     *
     * @return string
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        $queryString = [
            'feed' => $this->embedId,
        ];

        if ($options['autoplay']) {
            $queryString['autoplay'] = (int) $options['autoplay'];
            $queryString['playsinline'] = (int) $options['autoplay'];
        }
        if ($options['start']) {
            $queryString['start'] = (int) $options['start'];
        }
        if ($options['end']) {
            $queryString['end'] = (int) $options['end'];
        }
        if ($options['mini'] === true) {
            $queryString['mini'] = 1;
        }
        if ($options['hide_cover'] === true) {
            $queryString['hide_cover'] = 1;
        }
        if ($options['hide_artwork'] === true) {
            $queryString['hide_artwork'] = 1;
        }
        if ($options['light'] === true) {
            $queryString['light'] = 1;
        }

        return 'https://www.mixcloud.com/widget/iframe/?' . http_build_query($queryString);
    }
}
