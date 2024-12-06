<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Util to grab a facebook profile picture from userAlias.
 */
final readonly class FacebookPictureFinder
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    /**
     * @return string|null Facebook profile image URL
     *
     * @throws TransportExceptionInterface
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     */
    public function getPictureUrl(string $facebookUserAlias): ?string
    {
        $response = $this->client->request('GET', 'https://graph.facebook.com/'.$facebookUserAlias.'/picture?redirect=false&width=200&height=200');
        $json = json_decode($response->getContent(), true);

        if (is_array($json) && isset($json['data']) && !empty($json['data']['url'])) {
            return $json['data']['url'];
        }

        return null;
    }
}
