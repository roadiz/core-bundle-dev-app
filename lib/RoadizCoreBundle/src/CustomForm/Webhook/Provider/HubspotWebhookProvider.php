<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * HubSpot webhook provider.
 */
final readonly class HubspotWebhookProvider extends AbstractCustomFormWebhookProvider
{
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        #[\SensitiveParameter]
        private ?string $apiKey = null,
    ) {
        parent::__construct($httpClient, $logger);
    }

    #[\Override]
    public function getName(): string
    {
        return 'hubspot';
    }

    #[\Override]
    public function getDisplayName(): string
    {
        return 'HubSpot';
    }

    #[\Override]
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    #[\Override]
    public function getConfigSchema(): array
    {
        return [];
    }

    #[\Override]
    public function sendWebhook(
        CustomFormAnswer $answer,
        array $fieldMapping = [],
        array $extraConfig = [],
    ): bool {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('HubSpot webhook provider is not configured. Set APP_HUBSPOT_WEBHOOK_KEY environment variable.');
        }

        $mappedData = $this->mapAnswerData($answer, $fieldMapping);

        // Extract email from mapped data or answer
        $email = $mappedData['email'] ?? $answer->getEmail();
        if (!$email || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Valid email is required to send to HubSpot');
        }

        // Build contact properties for HubSpot v3 API
        $properties = [];
        foreach ($mappedData as $key => $value) {
            if (!str_starts_with($key, '_')) {
                $properties[$key] = $value;
            }
        }

        $payload = [
            'properties' => $properties,
        ];

        try {
            // Use HubSpot CRM v3 API to create or update contact
            $response = $this->httpClient->request('POST', 'https://api.hubapi.com/crm/v3/objects/contacts', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logSuccess($answer, 'Contact sent to HubSpot successfully');

                return true;
            }

            $this->logError($answer, sprintf('HubSpot API returned status code: %d', $statusCode));

            return false;
        } catch (\Throwable $e) {
            $this->logError($answer, 'Failed to send webhook to HubSpot: '.$e->getMessage(), $e);
            throw $e;
        }
    }
}
