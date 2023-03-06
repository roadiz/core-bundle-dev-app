<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

abstract class AbstractUnsplashPictureFinder extends AbstractEmbedFinder implements RandomImageFinder
{
    protected Client $client;
    /**
     * @var string
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'unsplash';

    public function getShortType(): string
    {
        return 'documents';
    }

    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return false;
    }

    public static function getPlatform(): string
    {
        return static::$platform;
    }

    /**
     * @param string $clientId
     * @param string $embedId
     */
    public function __construct(string $clientId, string $embedId = '')
    {
        parent::__construct($embedId);

        $this->client = new Client(
            [
            // Base URI is used with relative requests
            'base_uri' => 'https://api.unsplash.com',
            // You can set any number of default request options.
            'timeout' => 3.0,
            'headers' => [
                'Authorization' => 'Client-ID ' . $clientId
            ]
            ]
        );
    }

    protected function validateEmbedId(string $embedId = ""): string
    {
        return $embedId;
    }

    /**
     * @see    https://unsplash.com/documentation#get-a-random-photo
     * @param  array $options
     * @return array|null
     * @throws GuzzleException
     */
    public function getRandom(array $options = []): ?array
    {
        try {
            $response = $this->client->get(
                '/photos/random',
                [
                'query' => array_merge(
                    [
                    'content_filter' => 'high',
                    'orientation' => 'landscape'
                    ],
                    $options
                )
                ]
            );
            $feed = json_decode($response->getBody()->getContents(), true) ?? null;
            if (!is_array($feed)) {
                return null;
            }

            $url = $this->getBestUrl($feed);

            if (null !== $url) {
                $this->embedId = (string) $feed['id'];
                $this->feed = $feed;
                return $this->feed;
            }
            return null;
        } catch (ClientException $e) {
            return null;
        }
    }

    /**
     * @param  string $keyword
     * @param  array  $options
     * @return array|null
     * @throws GuzzleException
     */
    public function getRandomBySearch(string $keyword, array $options = [])
    {
        return $this->getRandom(
            [
            'query' => $keyword
            ]
        );
    }


    /**
     * {@inheritdoc}
     */
    public function getMediaFeed($search = null)
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaTitle(): string
    {
        return $this->feed['description'] ?? '';
    }

    /**
     * @return int|null
     */
    public function getMediaWidth(): ?int
    {
        return $this->feed['width'] ?? null;
    }

    /**
     * @return int|null
     */
    public function getMediaHeight(): ?int
    {
        return $this->feed['height'] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaDescription(): string
    {
        return $this->feed['alt_description'] ?? '';
    }

    /**
     * {@inheritdoc}
     */
    public function getMediaCopyright(): string
    {
        if (isset($this->feed['user'])) {
            return trim(($this->feed['user']['name'] ?? '') . ', Unsplash', " \t\n\r\0\x0B-");
        }
        return 'Unsplash';
    }

    /**
     * @inheritdoc
     * @throws     GuzzleException
     */
    public function getThumbnailURL(): ?string
    {
        if (null === $this->feed) {
            $feed = $this->getRandom();

            if (null === $feed) {
                return null;
            }
        }
        if (is_array($this->feed)) {
            return $this->getBestUrl($this->feed);
        }
        return null;
    }

    /**
     * @param array|null $feed
     *
     * @return string|null
     */
    protected function getBestUrl(?array $feed): ?string
    {
        if (null === $feed) {
            return null;
        }
        return $feed['urls']['full'] ?? $feed['urls']['raw'] ?? null;
    }
}
