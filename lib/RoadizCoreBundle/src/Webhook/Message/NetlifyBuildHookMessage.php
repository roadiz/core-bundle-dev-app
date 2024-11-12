<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use RZ\Roadiz\CoreBundle\Message\AsyncMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final class NetlifyBuildHookMessage implements AsyncMessage, HttpRequestMessage, WebhookMessage
{
    public function __construct(
        private readonly string $uri,
        private readonly ?array $payload = null,
    ) {
    }

    public function getRequest(): RequestInterface
    {
        if (null !== $this->payload) {
            return new Request(
                'POST',
                $this->uri,
                [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json',
                ],
                http_build_query($this->payload)
            );
        }

        return new Request('POST', $this->uri);
    }

    public function getOptions(): array
    {
        return [
            'debug' => false,
            'timeout' => 3,
        ];
    }

    /**
     * @return static
     */
    public static function fromWebhook(WebhookInterface $webhook): self
    {
        return new self($webhook->getUri(), $webhook->getPayload());
    }
}
