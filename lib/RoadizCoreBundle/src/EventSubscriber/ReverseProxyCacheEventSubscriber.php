<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EventSubscriber;

use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Event\Cache\CachePurgeRequestEvent;
use RZ\Roadiz\CoreBundle\Event\NodesSources\NodesSourcesUpdatedEvent;
use RZ\Roadiz\CoreBundle\Message\GuzzleRequestMessage;
use RZ\Roadiz\CoreBundle\Message\PurgeReverseProxyCacheMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\Event\Event;

final class ReverseProxyCacheEventSubscriber implements EventSubscriberInterface
{
    private ReverseProxyCacheLocator $reverseProxyCacheLocator;
    private LoggerInterface $logger;
    private MessageBusInterface $bus;

    /**
     * @param ReverseProxyCacheLocator $reverseProxyCacheLocator
     * @param MessageBusInterface $bus
     * @param LoggerInterface $logger
     */
    public function __construct(
        ReverseProxyCacheLocator $reverseProxyCacheLocator,
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->bus = $bus;
        $this->reverseProxyCacheLocator = $reverseProxyCacheLocator;
    }
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            CachePurgeRequestEvent::class => ['onBanRequest', 3],
            NodesSourcesUpdatedEvent::class => ['onPurgeRequest', 3],
            'workflow.node.completed' => ['onNodeWorkflowCompleted', 3],
        ];
    }

    /**
     * @return bool
     */
    protected function supportConfig(): bool
    {
        return count($this->reverseProxyCacheLocator->getFrontends()) > 0;
    }

    /**
     * @param Event $event
     */
    public function onNodeWorkflowCompleted(Event $event): void
    {
        $node = $event->getSubject();
        if ($node instanceof Node) {
            if (!$this->supportConfig()) {
                return;
            }
            foreach ($node->getNodeSources() as $nodeSource) {
                $this->purgeNodesSources($nodeSource);
            }
        }
    }

    /**
     * @param CachePurgeRequestEvent $event
     */
    public function onBanRequest(CachePurgeRequestEvent $event): void
    {
        if (!$this->supportConfig()) {
            return;
        }

        foreach ($this->createBanRequests() as $name => $request) {
            $this->sendRequest($request);
            $event->addMessage(
                'Reverse proxy cache cleared.',
                self::class,
                'Reverse proxy cache [' . $name . ']'
            );
        }
    }

    /**
     * @param NodesSourcesUpdatedEvent $event
     */
    public function onPurgeRequest(NodesSourcesUpdatedEvent $event): void
    {
        if (!$this->supportConfig()) {
            return;
        }

        $this->purgeNodesSources($event->getNodeSource());
    }

    /**
     * @return Request[]
     */
    protected function createBanRequests(): array
    {
        $requests = [];
        foreach ($this->reverseProxyCacheLocator->getFrontends() as $frontend) {
            // Add protocol if host does not start with it
            if (!\str_starts_with($frontend->getHost(), 'http')) {
                // Use HTTP to be able to call Varnish from a Docker network
                $uri = 'http://' . $frontend->getHost();
            } else {
                $uri = $frontend->getHost();
            }
            $requests[$frontend->getName()] = new Request(
                'BAN',
                $uri,
                [
                    'Host' => $frontend->getDomainName()
                ]
            );
        }
        return $requests;
    }

    /**
     * @param NodesSources $nodeSource
     */
    protected function purgeNodesSources(NodesSources $nodeSource): void
    {
        try {
            $this->bus->dispatch(new Envelope(new PurgeReverseProxyCacheMessage($nodeSource->getId())));
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * @param Request $request
     * @return void
     */
    protected function sendRequest(Request $request): void
    {
        try {
            $this->bus->dispatch(new Envelope(new GuzzleRequestMessage($request, [
                'debug' => false,
                'timeout' => 3
            ])));
        } catch (ExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
