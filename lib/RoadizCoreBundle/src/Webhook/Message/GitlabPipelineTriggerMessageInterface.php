<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use RZ\Roadiz\CoreBundle\Message\AsyncMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessageInterface;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final readonly class GitlabPipelineTriggerMessageInterface implements AsyncMessage, HttpRequestMessageInterface, WebhookMessage
{
    public function __construct(
        private string $uri,
        private string $token,
        private string $ref = 'main',
        private ?array $variables = null,
    ) {
    }

    public function getOptions(): array
    {
        $postBody = [
            'token' => $this->token,
            'ref' => $this->ref,
        ];
        if (null !== $this->variables) {
            $postBody['variables'] = $this->variables;
        }

        return [
            'timeout' => 3,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            'body' => http_build_query($postBody),
        ];
    }

    /**
     * @return static
     */
    public static function fromWebhook(WebhookInterface $webhook): self
    {
        $payload = $webhook->getPayload();

        return new self(
            $webhook->getUri(),
            $payload['token'] ?? '',
            $payload['ref'] ?? 'main',
            $payload['variables'] ?? []
        );
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
