<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId;

use CoderCat\JWKToPEM\Exception\Base64DecodeException;
use CoderCat\JWKToPEM\Exception\JWKConverterException;
use CoderCat\JWKToPEM\JWKConverter;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Bag\LazyParameterBag;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @package RZ\Roadiz\OpenId
 * @see https://accounts.google.com/.well-known/openid-configuration
 */
class Discovery extends LazyParameterBag
{
    public const CACHE_KEY = 'rz_openid_discovery_parameters';
    protected ?array $jwksData = null;

    public function __construct(
        protected readonly string $discoveryUri,
        protected readonly CacheItemPoolInterface $cacheAdapter,
        protected readonly HttpClientInterface $client,
        protected readonly LoggerInterface $logger,
    ) {
        parent::__construct();
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
                $client = $this->client->withOptions([
                    'timeout' => 2.0,
                ]);
                $response = $client->request('GET', $this->discoveryUri);
                /** @var array $parameters */
                $parameters = \json_decode(json: $response->getContent(), associative: true, flags: JSON_THROW_ON_ERROR);
                $cacheItem->set($parameters);
                $this->cacheAdapter->save($cacheItem);
            } catch (ExceptionInterface $exception) {
                $this->logger->warning('Cannot fetch OpenID discovery parameters: ' . $exception->getMessage());
                return;
            } catch (\JsonException $exception) {
                $this->logger->warning('Cannot fetch OpenID discovery parameters: ' . $exception->getMessage());
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
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws JWKConverterException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
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
     * @throws ClientExceptionInterface
     * @throws InvalidArgumentException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \JsonException
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
                $client = $this->client->withOptions([
                    'timeout' => 2.0,
                ]);
                $response = $client->request('GET', $jwksUri);
                $data = \json_decode(json: $response->getContent(), associative: true, flags: JSON_THROW_ON_ERROR);
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
