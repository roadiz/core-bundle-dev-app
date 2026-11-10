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
        private ?string $secret = null,
    ) {
    }

    #[\Override]
    public function getOptions(): array
    {
        $body = \json_encode($this->payload ?? [], JSON_NUMERIC_CHECK | JSON_THROW_ON_ERROR);
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if (null !== $this->secret && '' !== $this->secret) {
            $headers['X-Roadiz-Signature'] = 'sha256='.\hash_hmac('sha256', $body, $this->secret);
        }

        return [
            'timeout' => 3,
            'body' => $body,
            'headers' => $headers,
        ];
    }

    /**
     * @param Webhook $webhook
     */
    #[\Override]
    public static function fromWebhook(WebhookInterface $webhook): self
    {
        return new self(
            $webhook->getUri() ?? throw new \InvalidArgumentException('Webhook URI cannot be null.'),
            $webhook->getPayload(),
            $webhook->getSecret(),
        );
    }

    #[\Override]
    public function getMethod(): string
    {
        return 'POST';
    }

    #[\Override]
    public function getUri(): string
    {
        return $this->uri;
    }
}
