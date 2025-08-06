<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractMixcloudEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'mixcloud';
    protected static string $idPattern = '#^https\:\/\/www\.mixcloud\.com\/(?<author>[a-zA-Z0-9\-]+)\/(?<id>[a-zA-Z0-9\-]+)\/?$#';

    #[\Override]
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://www.mixcloud.com');
    }

    #[\Override]
    public static function getPlatform(): string
    {
        return static::$platform;
    }

    #[\Override]
    protected function validateEmbedId(string $embedId = ''): string
    {
        if (1 === preg_match(static::$idPattern, $embedId, $matches)) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    #[\Override]
    public function getMediaFeed(?string $search = null): string
    {
        $endpoint = 'https://www.mixcloud.com/oembed/';
        $query = [
            'url' => $this->embedId,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint.'?'.http_build_query($query));
    }

    #[\Override]
    public function getMediaTitle(): string
    {
        return $this->getFeed()['title'] ?? '';
    }

    #[\Override]
    public function getMediaDescription(): string
    {
        return $this->getFeed()['description'] ?? '';
    }

    #[\Override]
    public function getMediaCopyright(): string
    {
        return ($this->getFeed()['author_name'] ?? '').' ('.($this->getFeed()['author_url'] ?? '').')';
    }

    #[\Override]
    public function getThumbnailURL(): string
    {
        return $this->getFeed()['image'] ?? '';
    }

    #[\Override]
    public function getThumbnailName(string $pathinfo): string
    {
        if (1 === preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $ext)) {
            $pathinfo = '.'.$ext['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (1 === preg_match(static::$idPattern, $this->embedId, $matches)) {
            return $matches['author'].'_'.$matches['id'].$pathinfo;
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
     */
    #[\Override]
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
        if (true === $options['mini']) {
            $queryString['mini'] = 1;
        }
        if (true === $options['hide_cover']) {
            $queryString['hide_cover'] = 1;
        }
        if (true === $options['hide_artwork']) {
            $queryString['hide_artwork'] = 1;
        }
        if (true === $options['light']) {
            $queryString['light'] = 1;
        }

        return 'https://www.mixcloud.com/widget/iframe/?'.http_build_query($queryString);
    }

    #[\Override]
    protected function areDuplicatesAllowed(): bool
    {
        return true;
    }
}
