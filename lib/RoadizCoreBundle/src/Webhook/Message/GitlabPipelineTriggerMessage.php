<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Webhook\Message;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use RZ\Roadiz\CoreBundle\Message\AsyncMessage;
use RZ\Roadiz\CoreBundle\Message\HttpRequestMessage;
use RZ\Roadiz\CoreBundle\Webhook\WebhookInterface;

final class GitlabPipelineTriggerMessage implements AsyncMessage, HttpRequestMessage, WebhookMessage
{
    public function __construct(
        private readonly string $uri,
        private readonly string $token,
        private readonly string $ref = 'main',
        private readonly ?array $variables = null,
    ) {
    }

    public function getRequest(): RequestInterface
    {
        $postBody = [
            'token' => $this->token,
            'ref' => $this->ref,
        ];
        if (null !== $this->variables) {
            $postBody['variables'] = $this->variables;
        }

        return new Request(
            'POST',
            $this->uri,
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
            ],
            http_build_query($postBody)
        );
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
        $payload = $webhook->getPayload();

        return new self(
            $webhook->getUri(),
            $payload['token'] ?? '',
            $payload['ref'] ?? 'main',
            $payload['variables'] ?? []
        );
    }
}
