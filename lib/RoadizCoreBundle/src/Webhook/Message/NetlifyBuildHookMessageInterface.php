<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final readonly class NetlifyBuildHookMessageInterface implements AsyncMessage, HttpRequestMessageInterface, WebhookMessage
{
    public function __construct(
        private string $uri,
        private ?array $payload = null,
    ) {
    }

    public function getOptions(): array
    {
        if (null === $this->payload) {
            return [
                'timeout' => 3,
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ];
        }

        return [
            'timeout' => 3,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'body' => http_build_query($this->payload),
        ];
    }

    /**
     * @return static
     */
    public static function fromWebhook(WebhookInterface $webhook): self
    {
        return new self($webhook->getUri(), $webhook->getPayload());
    }

    public function getMethod(): string
    {
        return 'POST';
    }

    public function getUri(): string
    {
        return $this->uri;
    }
}
