<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Psr\Http\Message\StreamInterface;
use RZ\Roadiz\Documents\Exceptions\APINeedsAuthentificationException;
use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;

/**
 * Youtube tools class.
 */
abstract class AbstractYoutubeEmbedFinder extends AbstractEmbedFinder
{
    protected const YOUTUBE_EMBED_DOMAIN = 'https://www.youtube-nocookie.com';
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'youtube';
    protected static string $idPattern = '#^https\:\/\/(?:www\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v\=)?(?<id>[a-zA-Z0-9\_\-]+)#';
    protected static string $realIdPattern = '#^(?<id>[a-zA-Z0-9\_\-]+)$#';
    protected ?string $embedUrl;

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return str_starts_with($embedUrl, 'https://www.youtube.com/')
            || str_starts_with($embedUrl, 'https://youtube.com/')
            || str_starts_with($embedUrl, 'https://youtu.be/');
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

    public function getMediaFeed(?string $search = null): StreamInterface
    {
        if (preg_match(static::$realIdPattern, $this->embedId, $matches)) {
            $url = 'https://www.youtube.com/watch?v='.$this->embedId;
        } else {
            $url = $this->embedId;
        }
        $endpoint = 'https://www.youtube.com/oembed';
        $query = [
            'url' => $url,
            'format' => 'json',
        ];

        return $this->downloadFeedFromAPI($endpoint.'?'.http_build_query($query));
    }

    public function getFeed(): array|\SimpleXMLElement|null
    {
        $feed = parent::getFeed();
        /*
         * We need to extract REAL embedId from oEmbed response, from the HTML field.
         */
        $this->embedUrl = $this->embedId;
        if (
            is_array($feed)
            && !empty($feed['html'])
            && preg_match('#src\=\"https\:\/\/www\.youtube\.com\/embed\/(?<realId>[a-zA-Z0-9\_\-]+)#', $feed['html'], $matches)
        ) {
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
        $feed = $this->getFeed();

        return (is_array($feed) && isset($feed['description'])) ? ($feed['description']) : ('');
    }

    public function getMediaCopyright(): string
    {
        return ($this->getFeed()['author_name'] ?? '').' ('.($this->getFeed()['author_url'] ?? '').')';
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
            return 'youtube_'.$matches['id'].$pathinfo;
        }
        if (1 === preg_match(static::$idPattern, $embed, $matches)) {
            return 'youtube_'.$matches['id'].$pathinfo;
        }
        throw new InvalidEmbedId($embed, static::$platform);
    }

    /**
     * @throws APINeedsAuthentificationException
     */
    public function getSearchFeed(string $searchTerm, ?string $author = null, int $maxResults = 15): ?StreamInterface
    {
        if (null !== $this->getKey() && '' != $this->getKey()) {
            $url = 'https://www.googleapis.com/youtube/v3/search?q='.$searchTerm.'&part=snippet&key='.$this->getKey().'&maxResults='.$maxResults;
            if (!empty($author)) {
                $url .= '&author='.$author;
            }

            return $this->downloadFeedFromAPI($url);
        } else {
            throw new APINeedsAuthentificationException('YoutubeEmbedFinder needs a Google server key, create a “google_server_id” setting.', 1);
        }
    }

    /**
     * Get embed media source URL.
     *
     * ### Youtube additional embed parameters
     *
     * * modestbrandin
     * * rel
     * * showinfo
     * * start
     * * enablejsapi
     * * muted
     */
    public function getSource(array &$options = []): string
    {
        parent::getSource($options);

        $queryString = [
            'rel' => 0,
            'html5' => 1,
            'wmode' => 'transparent',
        ];

        if ($options['autoplay']) {
            $queryString['autoplay'] = (int) $options['autoplay'];
            $queryString['playsinline'] = (int) $options['autoplay'];
        }
        if ($options['playsinline']) {
            $queryString['playsinline'] = (int) $options['playsinline'];
        }
        if ($options['playlist']) {
            $queryString['playlist'] = (int) $options['playlist'];
        }
        if (null !== $options['color']) {
            $queryString['color'] = $options['color'];
        }
        if ($options['start']) {
            $queryString['start'] = (int) $options['start'];
        }
        if ($options['end']) {
            $queryString['end'] = (int) $options['end'];
        }

        $queryString['loop'] = (int) $options['loop'];
        $queryString['controls'] = (int) $options['controls'];
        $queryString['fs'] = (int) $options['fullscreen'];
        $queryString['modestbranding'] = (int) $options['modestbranding'];
        $queryString['rel'] = (int) $options['rel'];
        $queryString['showinfo'] = (int) $options['showinfo'];
        $queryString['enablejsapi'] = (int) $options['enablejsapi'];
        $queryString['mute'] = (int) $options['muted'];

        return static::YOUTUBE_EMBED_DOMAIN.'/embed/'.$this->embedId.'?'.http_build_query($queryString);
    }
}
