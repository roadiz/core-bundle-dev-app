<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;

/**
 * Generic HTTP webhook provider for custom endpoints.
 */
final readonly class GenericHttpWebhookProvider extends AbstractCustomFormWebhookProvider
{
    #[\Override]
    public function getName(): string
    {
        return 'generic_http';
    }

    #[\Override]
    public function getDisplayName(): string
    {
        return 'Generic HTTP Webhook';
    }

    #[\Override]
    public function isConfigured(): bool
    {
        // Generic HTTP provider is always "configured" as it requires URL in extraConfig
        return true;
    }

    #[\Override]
    public function getConfigSchema(): array
    {
        return [
            'url' => [
                'type' => 'url',
                'label' => 'Webhook URL',
                'required' => true,
                'help' => 'The HTTP endpoint to send the webhook to',
            ],
            'method' => [
                'type' => 'choice',
                'label' => 'HTTP Method',
                'required' => false,
                'help' => 'HTTP method to use (default: POST)',
                'choices' => [
                    'POST' => 'POST',
                    'PUT' => 'PUT',
                    'PATCH' => 'PATCH',
                ],
            ],
            'auth_header' => [
                'type' => 'text',
                'label' => 'Authorization Header',
                'required' => false,
                'help' => 'Optional authorization header value (e.g., "Bearer token123"). Be careful, data will be stored in clear in database.',
            ],
        ];
    }

    #[\Override]
    public function sendWebhook(
        CustomFormAnswer $answer,
        array $fieldMapping = [],
        array $extraConfig = [],
    ): bool {
        if (empty($extraConfig['url'])) {
            throw new \InvalidArgumentException('url is required in extraConfig for Generic HTTP provider');
        }

        $mappedData = $this->mapAnswerData($answer, $fieldMapping);
        $method = $extraConfig['method'] ?? 'POST';

        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $mappedData,
        ];

        if (!empty($extraConfig['auth_header'])) {
            $options['headers']['Authorization'] = $extraConfig['auth_header'];
        }

        try {
            $response = $this->httpClient->request($method, $extraConfig['url'], $options);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logSuccess($answer, sprintf('Webhook sent to %s successfully', $extraConfig['url']));

                return true;
            }

            $this->logError($answer, sprintf('Webhook endpoint returned status code: %d', $statusCode));

            return false;
        } catch (\Throwable $e) {
            $this->logError($answer, 'Failed to send generic HTTP webhook: '.$e->getMessage(), $e);
            throw $e;
        }
    }
}
