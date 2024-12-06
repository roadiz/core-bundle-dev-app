<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

/**
 * Manage a dailymotion video feed.
 */
abstract class AbstractDailymotionEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'dailymotion';
    protected static string $idPattern = '#^https\:\/\/(?:www\.)?(?:dailymotion\.com|dai\.ly)\/video\/(?<id>[a-zA-Z0-9\_\-]+)#';
    protected static string $realIdPattern = '#^(?<id>[a-zA-Z0-9\_\-]+)$#';
    protected ?string $embedUrl;

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://dailymotion.com')
            || str_starts_with($embedUrl, 'https://www.dailymotion.com');
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
        if (1 === preg_match(static::$realIdPattern, $embedId, $matches)) {
            return $embedId;
        }
        throw new InvalidEmbedId($embedId, static::$platform);
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

    public function getFeed(): array|\SimpleXMLElement|null
    {
        $oEmbedIframePattern = '#src\=\"https\:\/\/(?:www\.|geo\.)?dailymotion\.com\/(?:embed\/video\/|player\.html\?video\=)(?<realId>[a-zA-Z0-9\_\-]+)#';
        $feed = parent::getFeed();
        /*
         * We need to extract REAL embedId from oEmbed response, from the HTML field.
         */
        $this->embedUrl = $this->embedId;
        if (
            is_array($feed)
            && !empty($feed['html'])
            && preg_match($oEmbedIframePattern, $feed['html'], $matches)
        ) {
            $this->embedId = urldecode($matches['realId']);
        }

        return $feed;
    }

    public function getMediaFeed(?string $search = null): string
    {
        if (preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            $url = 'https://www.dailymotion.com/video/'.$this->embedId;
        } else {
            $url = $this->embedId;
        }

        $endpoint = 'https://www.dailymotion.com/services/oembed';
        $query = [
            'url' => $url,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint.'?'.http_build_query($query));
    }

    public function getThumbnailName(string $pathinfo): string
    {
        if (null === $this->embedUrl) {
            $embed = $this->embedId;
        } else {
            $embed = $this->embedUrl;
        }
        if (1 === preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $matches)) {
            $pathinfo = '.'.$matches['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (1 === preg_match(static::$realIdPattern, $embed, $matches)) {
            return 'dailymotion_'.$matches['id'].$pathinfo;
        }
        if (1 === preg_match(static::$idPattern, $embed, $matches)) {
            return 'dailymotion_'.$matches['id'].$pathinfo;
        }
        throw new InvalidEmbedId($embed, static::$platform);
    }

    /**
     * Get embed media source URL.
     *
     * ## Available fields
     *
     * * loop
     * * autoplay
     * * controls
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        $queryString = [];

        $queryString['autoplay'] = (int) $options['autoplay'];
        $queryString['loop'] = (int) $options['loop'];
        $queryString['controls'] = (int) $options['controls'];
        $queryString['muted'] = (int) $options['muted'];
        $queryString['video'] = $this->embedId;

        return 'https://geo.dailymotion.com/player.html?'.http_build_query($queryString);
    }
}
