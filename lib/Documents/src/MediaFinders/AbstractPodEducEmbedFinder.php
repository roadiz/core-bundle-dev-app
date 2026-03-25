<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractPodEducEmbedFinder extends AbstractEmbedFinder
{
    private const string PODEDUC_BASE_URL = 'https://podeduc.apps.education.fr';
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'podeduc';
    protected static string $videoUrlPattern = '~^https://podeduc\.apps\.education\.fr/video/(?<id>[^/?#]+)(?:/)?(?:\?.*)?$~';

    #[\Override]
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, self::PODEDUC_BASE_URL.'/video/');
    }

    #[\Override]
    public static function getPlatform(): string
    {
        return static::$platform;
    }

    #[\Override]
    protected function validateEmbedId(string $embedId = ''): string
    {
        if (1 === preg_match(static::$videoUrlPattern, $embedId, $matches)) {
            return self::PODEDUC_BASE_URL.'/video/'.$matches['id'].'/';
        }

        if (str_starts_with($embedId, self::PODEDUC_BASE_URL.'/video/oembed/')) {
            $parsedUrl = parse_url($embedId);
            if (is_array($parsedUrl) && isset($parsedUrl['query'])) {
                parse_str($parsedUrl['query'], $query);
                if (isset($query['url']) && is_string($query['url']) && 1 === preg_match(static::$videoUrlPattern, $query['url'], $matches)) {
                    return self::PODEDUC_BASE_URL.'/video/'.$matches['id'].'/';
                }
            }
        }

        throw new InvalidEmbedId($embedId, static::$platform);
    }

    #[\Override]
    public function getMediaFeed(?string $search = null): string
    {
        $endpoint = self::PODEDUC_BASE_URL.'/video/oembed/';
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
        return ($this->getFeed()['author_name'] ?? '').' ('.($this->getFeed()['provider_name'] ?? '').')';
    }

    #[\Override]
    public function getThumbnailURL(): string
    {
        $thumbnailUrl = $this->getFeed()['thumbnail_url'] ?? '';
        if (!is_string($thumbnailUrl) || '' === $thumbnailUrl) {
            return '';
        }
        if (str_starts_with($thumbnailUrl, self::PODEDUC_BASE_URL) || str_starts_with($thumbnailUrl, 'https://')) {
            return $thumbnailUrl;
        }
        if (str_starts_with($thumbnailUrl, '/')) {
            return self::PODEDUC_BASE_URL.$thumbnailUrl;
        }
        if (str_starts_with($thumbnailUrl, 'https:/')) {
            $path = substr($thumbnailUrl, strlen('https:'));
            if (str_starts_with($path, '/')) {
                return self::PODEDUC_BASE_URL.$path;
            }
        }

        return $thumbnailUrl;
    }

    #[\Override]
    public function getMediaWidth(): ?int
    {
        $width = $this->getFeed()['width'] ?? null;

        return is_numeric($width) ? (int) $width : null;
    }

    #[\Override]
    public function getMediaHeight(): ?int
    {
        $height = $this->getFeed()['height'] ?? null;

        return is_numeric($height) ? (int) $height : null;
    }

    #[\Override]
    public function getThumbnailName(string $pathinfo): string
    {
        if (1 === preg_match('#\.(?<extension>[jpe?g|png|gif])$#', $pathinfo, $matches)) {
            $pathinfo = '.'.$matches['extension'];
        } else {
            $pathinfo = '.jpg';
        }
        if (1 === preg_match(static::$videoUrlPattern, $this->embedId, $matches)) {
            return 'podeduc_'.$matches['id'].$pathinfo;
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

        $queryString = [
            'is_iframe' => 'true',
        ];

        if ($options['autoplay']) {
            $queryString['autoplay'] = (int) $options['autoplay'];
        }
        if ($options['loop']) {
            $queryString['loop'] = (int) $options['loop'];
        }
        if ($options['start']) {
            $queryString['start'] = (int) $options['start'];
        }

        return $this->embedId.'?'.http_build_query($queryString);
    }
}
