<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractTwitchEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'twitch';
    protected static string $idPattern = '#^https\:\/\/(www\.)?twitch\.tv\/videos\/(?<id>[0-9]+)#';

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://twitch.tv')
            || str_starts_with($embedUrl, 'https://www.twitch.tv');
    }

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    protected function validateEmbedId(string $embedId = ''): string
    {
        if (1 === preg_match(static::$idPattern, $embedId, $matches)) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    public function getMediaFeed(?string $search = null): string
    {
        $endpoint = 'https://api.twitch.tv/v4/oembed';
        $query = [
            'url' => $this->embedId,
        ];

        return $this->downloadFeedFromAPI($endpoint.'?'.http_build_query($query));
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
        return ($this->getFeed()['author_name'] ?? '').' - '.($this->getFeed()['provider_name'] ?? '').' ('.($this->getFeed()['author_url'] ?? '').')';
    }

    public function getThumbnailURL(): string
    {
        return $this->getFeed()['thumbnail_url'] ?? '';
    }

    public function getThumbnailName(string $pathinfo): string
    {
        if (1 === preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $ext)) {
            $pathinfo = '.'.$ext['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (1 === preg_match(static::$idPattern, $this->embedId, $matches)) {
            return 'twitch_'.$matches['id'].$pathinfo;
        }
        throw new InvalidEmbedId($this->embedId, static::$platform);
    }

    /**
     * Get embed media source URL.
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        if (preg_match(static::$idPattern, $this->embedId, $matches)) {
            $queryString = [
                'video' => $matches['id'],
                'branding' => 0,
            ];

            if ($options['autoplay']) {
                $queryString['autoplay'] = (int) $options['autoplay'];
                $queryString['playsinline'] = (int) $options['autoplay'];
            }

            return 'https://player.twitch.tv/?'.http_build_query($queryString);
        }

        return $this->embedId;
    }
}
