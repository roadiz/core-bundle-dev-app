<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class AbstractUnsplashPictureFinder extends AbstractEmbedFinder implements RandomImageFinder
{
    /**
     * @internal Use getPlatform() instead
     */
    protected static string $platform = 'unsplash';

    #[\Override]
    public function getShortType(): string
    {
        return 'documents';
    }

    #[\Override]
    public static function supportEmbedUrl(string $embedUrl): bool
    {
        return false;
    }

    #[\Override]
    public static function getPlatform(): string
    {
        return static::$platform;
    }

    public function __construct(HttpClientInterface $client, string $clientId, string $embedId = '')
    {
        parent::__construct($client->withOptions([
            // Base URI is used with relative requests
            'base_uri' => 'https://api.unsplash.com',
            // You can set any number of default request options.
            'timeout' => 3.0,
            'headers' => [
                'Authorization' => 'Client-ID '.$clientId,
            ],
        ]), $embedId);
    }

    #[\Override]
    protected function validateEmbedId(string $embedId = ''): string
    {
        return $embedId;
    }

    /**
     * @see https://unsplash.com/documentation#get-a-random-photo
     */
    #[\Override]
    public function getRandom(array $options = []): ?array
    {
        try {
            $response = $this->client->request(
                'GET',
                '/photos/random',
                [
                    'query' => array_merge(
                        [
                            'content_filter' => 'high',
                            'orientation' => 'landscape',
                        ],
                        $options
                    ),
                ]
            );
            $feed = json_decode($response->getContent(), true) ?? null;
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
        } catch (ClientExceptionInterface) {
            return null;
        }
    }

    #[\Override]
    public function getRandomBySearch(string $keyword, array $options = []): ?array
    {
        return $this->getRandom(
            [
                'query' => $keyword,
            ]
        );
    }

    #[\Override]
    public function getMediaFeed(?string $search = null): string
    {
        throw new \LogicException('Unsplash API does not provide a feed.');
    }

    #[\Override]
    public function getMediaTitle(): string
    {
        return $this->feed['description'] ?? '';
    }

    #[\Override]
    public function getMediaWidth(): ?int
    {
        return $this->feed['width'] ?? null;
    }

    #[\Override]
    public function getMediaHeight(): ?int
    {
        return $this->feed['height'] ?? null;
    }

    #[\Override]
    public function getMediaDescription(): string
    {
        return $this->feed['alt_description'] ?? '';
    }

    #[\Override]
    public function getMediaCopyright(): string
    {
        if (isset($this->feed['user'])) {
            return trim(($this->feed['user']['name'] ?? '').', Unsplash', " \t\n\r\0\x0B-");
        }

        return 'Unsplash';
    }

    #[\Override]
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

    protected function getBestUrl(?array $feed): ?string
    {
        if (null === $feed) {
            return null;
        }

        return $feed['urls']['full'] ?? $feed['urls']['raw'] ?? null;
    }
}
