<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Cache\ReverseProxyCacheLocator;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use RZ\Roadiz\CoreBundle\Message\PurgeReverseProxyCacheMessage;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[AsMessageHandler]
final readonly class PurgeReverseProxyCacheMessageHandler
{
    public function __construct(
        private MessageBusInterface $bus,
        private UrlGeneratorInterface $urlGenerator,
        private ReverseProxyCacheLocator $reverseProxyCacheLocator,
        private ManagerRegistry $managerRegistry,
    ) {
    }

    public function __invoke(PurgeReverseProxyCacheMessage $message): void
    {
        $nodeSource = $this->managerRegistry
            ->getRepository(NodesSources::class)
            ->setDisplayingAllNodesStatuses(true)
            ->find($message->getNodeSourceId());
        if (null === $nodeSource) {
            throw new UnrecoverableMessageHandlingException('NodesSources does not exist anymore.');
        }

        while (!$nodeSource->isReachable()) {
            $nodeSource = $nodeSource->getParent();
            if (null === $nodeSource) {
                return;
            }
        }

        $purgeRequests = $this->createPurgeRequests($this->urlGenerator->generate(
            RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
            [
                RouteObjectInterface::ROUTE_OBJECT => $nodeSource,
            ]
        ));
        foreach ($purgeRequests as $request) {
            $this->sendRequest($request);
        }
    }

    /**
     * @return HttpRequestMessageInterface[]
     */
    protected function createPurgeRequests(string $path = '/'): array
    {
        $requests = [];
        foreach ($this->reverseProxyCacheLocator->getFrontends() as $frontend) {
            $host = $frontend->getHost();
            str_starts_with($host, 'http') || $host = 'http://'.$host;
            $requests[$frontend->getName()] = new HttpRequestMessage(
                Request::METHOD_PURGE,
                $host.$path,
                [
                    'timeout' => 3,
                    'headers' => [
                        'Host' => $frontend->getDomainName(),
                    ],
                ]
            );
        }

        return $requests;
    }

    protected function sendRequest(HttpRequestMessageInterface $requestMessage): void
    {
        try {
            $this->bus->dispatch(new Envelope($requestMessage));
        } catch (NoHandlerForMessageException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        }
    }
}
