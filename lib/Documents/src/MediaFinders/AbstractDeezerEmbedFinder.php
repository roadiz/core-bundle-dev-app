<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

abstract class AbstractDeezerEmbedFinder extends AbstractEmbedFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'deezer';
    // https://www.deezer.com/fr/playlist/9313425622
    protected static string $idPattern = '#^https?:\/\/(www.)?deezer\.com\/(?:\\w+/)?(?<type>track|playlist|artist|podcast|episode|album)\/(?<id>[a-zA-Z0-9]+)#';
    protected static string $realIdPattern = '#^(?<type>track|playlist|artist|podcast|episode|album)\/(?<id>[a-zA-Z0-9]+)$#';
    protected ?string $embedUrl = null;

    #[\Override]
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://www.deezer.com');
    }

    #[\Override]
    public static function getPlatform(): string
    {
        return static::$platform;
    }

    #[\Override]
    public function isEmptyThumbnailAllowed(): bool
    {
        return true;
    }

    #[\Override]
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

    #[\Override]
    public function getMediaFeed(?string $search = null): string
    {
        if (preg_match(static::$realIdPattern, $this->embedId)) {
            $url = 'https://www.deezer.com/fr/'.$this->embedId;
        } else {
            $url = $this->embedId;
        }
        $endpoint = 'https://api.deezer.com/oembed';
        $query = [
            'url' => $url,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint.'?'.http_build_query($query));
    }

    #[\Override]
    public function getFeed(): array|\SimpleXMLElement|null
    {
        $feed = parent::getFeed();
        /*
         * We need to extract REAL embedId from oEmbed response, from the HTML field.
         */
        $this->embedUrl = $this->embedId;
        if (preg_match(static::$idPattern, $this->embedId, $matches)) {
            $this->embedId = $matches['type'].'/'.$matches['id'];
        }

        return $feed;
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
        return ($this->getFeed()['provider_name'] ?? '').' ('.($this->getFeed()['provider_url'] ?? '').')';
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
            return $matches['type'].'_'.$matches['id'].$pathinfo;
        }
        if (1 === preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            return $matches['type'].'_'.$matches['id'].$pathinfo;
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
            'id' => $this->embedId,
        ];

        if (key_exists('autoplay', $options)) {
            $queryString['autoplay'] = ((bool) $options['autoplay']) ? ('true') : ('false');
        }
        if ($options['width']) {
            $queryString['width'] = (int) $options['width'];
        }
        if ($options['height']) {
            $queryString['height'] = (int) $options['height'];
        }

        $queryString['loop'] = (int) $options['loop'];
        $queryString['controls'] = (int) $options['controls'];
        $queryString['fs'] = (int) $options['fullscreen'];
        $queryString['modestbranding'] = (int) $options['modestbranding'];
        $queryString['rel'] = (int) $options['rel'];
        $queryString['showinfo'] = (int) $options['showinfo'];
        $queryString['enablejsapi'] = (int) $options['enablejsapi'];
        $queryString['mute'] = (int) $options['muted'];

        if (preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            $baseUri = 'https://widget.deezer.com/widget/auto/'.$this->embedId;
        } elseif (preg_match(static::$idPattern, $this->embedId, $matches)) {
            $baseUri = 'https://widget.deezer.com/widget/auto/'.$matches['type'].'/'.$matches['id'];
        } else {
            $baseUri = 'https://widget.deezer.com/widget/auto/';
        }

        // https://widget.deezer.com/widget/dark/playlist/9313425622
        return $baseUri.'?'.http_build_query($queryString);
    }

    #[\Override]
    protected function areDuplicatesAllowed(): bool
    {
        return true;
    }
}
