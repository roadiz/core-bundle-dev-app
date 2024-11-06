<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Message\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler]
final class HttpRequestMessageHandler
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(HttpRequestMessage $message): void
    {
        try {
            $this->logger->debug(sprintf(
                'HTTP request executed: %s %s',
                $message->getRequest()->getMethod(),
                $message->getRequest()->getUri()
            ));
            $client = new Client();
            $client->send($message->getRequest(), $message->getOptions());
        } catch (GuzzleException $exception) {
            throw new UnrecoverableMessageHandlingException($exception->getMessage(), 0, $exception);
        }
    }
}
