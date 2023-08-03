<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId;

use CoderCat\JWKToPEM\Exception\Base64DecodeException;
use CoderCat\JWKToPEM\Exception\JWKConverterException;
use CoderCat\JWKToPEM\JWKConverter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Cache\CacheItemPoolInterface;
use RZ\Roadiz\Bag\LazyParameterBag;

/**
 * @package RZ\Roadiz\OpenId
 * @see https://accounts.google.com/.well-known/openid-configuration
 */
class Discovery extends LazyParameterBag
{
    public const CACHE_KEY = 'rz_openid_discovery_parameters';

    protected string $discoveryUri;
    protected CacheItemPoolInterface $cacheAdapter;
    protected ?array $jwksData = null;

    /**
     * @param string  $discoveryUri
     * @param CacheItemPoolInterface $cacheAdapter
     */
    public function __construct(string $discoveryUri, CacheItemPoolInterface $cacheAdapter)
    {
        parent::__construct();
        $this->discoveryUri = $discoveryUri;
        $this->cacheAdapter = $cacheAdapter;
    }

    public function isValid(): bool
    {
        return !empty($this->discoveryUri) && filter_var($this->discoveryUri, FILTER_VALIDATE_URL);
    }

    protected function populateParameters(): void
    {
        $cacheItem = $this->cacheAdapter->getItem(static::CACHE_KEY);
        if ($cacheItem->isHit()) {
            /** @var array $parameters */
            $parameters = $cacheItem->get();
        } else {
            try {
                $client = new Client([
                    // You can set any number of default request options.
                    'timeout'  => 2.0,
                ]);
                $response = $client->get($this->discoveryUri);
                /** @var array $parameters */
                $parameters = \json_decode($response->getBody()->getContents(), true);
                $cacheItem->set($parameters);
                $this->cacheAdapter->save($cacheItem);
            } catch (RequestException $exception) {
                return;
            }
        }

        $this->parameters = [];
        foreach ($parameters as $key => $parameter) {
            $this->parameters[$key] = $parameter;
        }
        $this->ready = true;
    }

    /**
     * @return bool
     */
    public function canVerifySignature(): bool
    {
        return $this->isValid() && $this->has('jwks_uri');
    }

    /**
     * @return array<string>|null
     * @throws Base64DecodeException
     * @throws JWKConverterException
     * @throws GuzzleException
     * @see https://auth0.com/docs/tokens/json-web-tokens/json-web-key-sets
     */
    public function getPems(): ?array
    {
        $jwksData = $this->getJwksData();
        if (null !== $jwksData && isset($jwksData['keys'])) {
            $converter = new JWKConverter();
            return $converter->multipleToPEM($jwksData['keys']);
        }
        return null;
    }

    /**
     * @return array|null
     * @throws GuzzleException
     */
    protected function getJwksData(): ?array
    {
        if (null === $this->jwksData && $this->has('jwks_uri')) {
            $jwksUri = $this->get('jwks_uri');
            if (!is_string($jwksUri) || empty($jwksUri)) {
                return null;
            }
            $cacheItem = $this->cacheAdapter->getItem('jwks_uri_' . \md5($jwksUri));
            if ($cacheItem->isHit()) {
                $data = $cacheItem->get();
                if (is_array($data)) {
                    $this->jwksData = $data;
                } else {
                    $this->jwksData = null;
                }
            } else {
                $client = new Client([
                    // You can set any number of default request options.
                    'timeout'  => 3.0,
                ]);
                $response = $client->get($jwksUri);
                $data = \json_decode($response->getBody()->getContents(), true);
                if (is_array($data)) {
                    $this->jwksData = $data;
                } else {
                    $this->jwksData = null;
                }
                $cacheItem->set($this->jwksData)->expiresAfter(3600);
                $this->cacheAdapter->save($cacheItem);
            }
        }
        return $this->jwksData;
    }
}
