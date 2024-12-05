<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use RZ\Roadiz\CoreBundle\Entity\Webhook;
use RZ\Roadiz\CoreBundle\Message\AsyncMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final readonly class GenericJsonPostMessageInterface implements AsyncMessage, HttpRequestMessageInterface, WebhookMessage
{
    public function __construct(
        private string $uri,
        private ?array $payload = null,
    ) {
    }

    public function getOptions(): array
    {
        return [
            'timeout' => 3,
            'body' => \json_encode($this->payload ?? [], JSON_NUMERIC_CHECK | JSON_THROW_ON_ERROR),
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ];
    }

    /**
     * @param Webhook $webhook
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
