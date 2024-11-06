<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Util to grab a facebook profile picture from userAlias.
 */
final class FacebookPictureFinder
{
    /**
     * @return string|null Facebook profile image URL
     *
     * @throws GuzzleException
     */
    public function getPictureUrl(string $facebookUserAlias): ?string
    {
        $client = new Client();
        $response = $client->get('https://graph.facebook.com/'.$facebookUserAlias.'/picture?redirect=false&width=200&height=200');
        $json = json_decode($response->getBody()->getContents(), true);

        if (is_array($json) && isset($json['data']) && !empty($json['data']['url'])) {
            return $json['data']['url'];
        }

        return null;
    }
}
