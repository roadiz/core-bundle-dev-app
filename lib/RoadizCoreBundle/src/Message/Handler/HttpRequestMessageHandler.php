<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class HttpRequestMessageHandler
{
    public function __construct(
        private HttpClientInterface $client,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(HttpRequestMessageInterface $message): void
    {
        try {
            $this->logger->debug(sprintf(
                'HTTP request executed: %s %s',
                $message->getMethod(),
                $message->getUri()
            ));
            $response = $this->client->request(
                $message->getMethod(),
                $message->getUri(),
                $message->getOptions(),
            );
            $response->getStatusCode();
        } catch (ClientExceptionInterface $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        }
    }
}
