<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\MediaFinders;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Util to grab a facebook profile picture from userAlias.
 */
class FacebookPictureFinder
{
    protected string $facebookUserAlias;
    protected ResponseInterface $response;

    /**
     * @param string $facebookUserAlias
     */
    public function __construct(string $facebookUserAlias)
    {
        $this->facebookUserAlias = $facebookUserAlias;
    }

    /**
     * @return string|null Facebook profile image URL
     * @throws GuzzleException
     */
    public function getPictureUrl(): ?string
    {
        $client = new Client();
        $this->response = $client->get('https://graph.facebook.com/' . $this->facebookUserAlias . '/picture?redirect=false&width=200&height=200');
        $json = json_decode($this->response->getBody()->getContents(), true);

        if (is_array($json) && isset($json['data']) && !empty($json['data']['url'])) {
            return $json['data']['url'];
        }
        return null;
    }
}
