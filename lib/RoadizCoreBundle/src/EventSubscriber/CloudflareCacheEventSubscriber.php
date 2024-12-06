<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\CloudflareProxyCache;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface as MessengerExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

final readonly class CloudflareCacheEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MessageBusInterface $bus,
        private ReverseProxyCacheLocator $reverseProxyCacheLocator,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CachePurgeRequestEvent::class => ['onBanRequest', 3],
            NodesSourcesUpdatedEvent::class => ['onPurgeRequest', 3],
        ];
    }

    protected function supportConfig(): bool
    {
        return null !== $this->reverseProxyCacheLocator->getCloudflareProxyCache();
    }

    public function onBanRequest(CachePurgeRequestEvent $event): void
    {
        if (!$this->supportConfig()) {
            return;
        }
        try {
            $request = $this->createBanRequest();
            $this->sendRequest($request);
            $event->addMessage(
                'Cloudflare cache cleared.',
                self::class,
                'Cloudflare proxy cache'
            );
        } catch (HttpExceptionInterface $e) {
            $data = \json_decode($e->getResponse()->getContent(false), true);
            $event->addError(
                $data['errors'][0]['message'] ?? $e->getMessage(),
                self::class,
                'Cloudflare proxy cache'
            );
        } catch (ExceptionInterface $e) {
            $event->addError(
                $e->getMessage(),
                self::class,
                'Cloudflare proxy cache'
            );
        }
    }

    public function onPurgeRequest(NodesSourcesUpdatedEvent $event): void
    {
        if (!$this->supportConfig()) {
            return;
        }

        try {
            $nodeSource = $event->getNodeSource();
            while (!$nodeSource->isReachable()) {
                $nodeSource = $nodeSource->getParent();
                if (null === $nodeSource) {
                    return;
                }
            }

            $purgeRequest = $this->createPurgeRequest([$this->urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            )]);
            $this->sendRequest($purgeRequest);
        } catch (ExceptionInterface $e) {
            // do nothing
        }
    }

    private function getCloudflareCacheProxy(): CloudflareProxyCache
    {
        $proxy = $this->reverseProxyCacheLocator->getCloudflareProxyCache();
        if (null === $proxy) {
            throw new \RuntimeException('Cloudflare cache proxy is not configured');
        }

        return $proxy;
    }

    /**
     * @throws \JsonException
     */
    protected function createRequest(array $body): HttpRequestMessageInterface
    {
        $headers = [
            'Content-type' => 'application/json',
        ];
        $headers['Authorization'] = 'Bearer '.trim($this->getCloudflareCacheProxy()->getBearer());
        $headers['X-Auth-Email'] = $this->getCloudflareCacheProxy()->getEmail();
        $headers['X-Auth-Key'] = $this->getCloudflareCacheProxy()->getKey();

        $uri = sprintf(
            'https://api.cloudflare.com/client/%s/zones/%s/purge_cache',
            $this->getCloudflareCacheProxy()->getVersion(),
            $this->getCloudflareCacheProxy()->getZone()
        );
        $body = \json_encode($body, JSON_THROW_ON_ERROR);

        return new HttpRequestMessage(
            'POST',
            $uri,
            [
                'timeout' => $this->getCloudflareCacheProxy()->getTimeout(),
                'headers' => $headers,
                'body' => $body,
            ],
        );
    }

    /**
     * @throws \JsonException
     */
    protected function createBanRequest(): HttpRequestMessageInterface
    {
        return $this->createRequest([
            'purge_everything' => true,
        ]);
    }

    /**
     * @param string[] $uris
     *
     * @throws \JsonException
     */
    protected function createPurgeRequest(array $uris = []): HttpRequestMessageInterface
    {
        return $this->createRequest([
            'files' => $uris,
        ]);
    }

    protected function sendRequest(HttpRequestMessageInterface $requestMessage): void
    {
        try {
            $this->bus->dispatch(new Envelope($requestMessage));
        } catch (MessengerExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
