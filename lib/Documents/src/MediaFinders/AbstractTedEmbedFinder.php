<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractTedEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'ted';
    protected static string $idPattern = '#^https\:\/\/(www\.)?ted\.com\/talks\/(?<id>[a-zA-Z0-9\-\_]+)#';

    #[\Override]
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://www.ted.com/talks');
    }

    #[\Override]
    public static function getPlatform(): string
    {
        return static::$platform;
    }

    #[\Override]
    protected function validateEmbedId(string $embedId = ''): string
    {
        if (preg_match(static::$idPattern, $embedId, $matches)) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
    }

    #[\Override]
    public function getMediaFeed(?string $search = null): string
    {
        $endpoint = 'https://www.ted.com/services/v1/oembed.json';
        $query = [
            'url' => $this->embedId,
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
        return ($this->getFeed()['author_name'] ?? '').' - '.($this->getFeed()['provider_name'] ?? '').' ('.($this->getFeed()['author_url'] ?? '').')';
    }

    #[\Override]
    public function getThumbnailURL(): string
    {
        return $this->getFeed()['thumbnail_url'] ?? '';
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
            return 'ted_talk_'.$matches['id'].$pathinfo;
        }
        throw new InvalidEmbedId($this->embedId, static::$platform);
    }

    /**
     * Get embed media source URL.
     */
    #[\Override]
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        if (preg_match(static::$idPattern, $this->embedId, $matches)) {
            return 'https://embed.ted.com/talks/'.$matches['id'];
        }

        return $this->embedId;
    }
}
