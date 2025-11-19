<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Zoho CRM webhook provider.
 */
final class ZohoCrmWebhookProvider extends AbstractCustomFormWebhookProvider
{
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        private readonly ?string $apiKey = null,
    ) {
        parent::__construct($httpClient, $logger);
    }

    #[\Override]
    public function getName(): string
    {
        return 'zoho_crm';
    }

    #[\Override]
    public function getDisplayName(): string
    {
        return 'Zoho CRM';
    }

    #[\Override]
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    #[\Override]
    public function getConfigSchema(): array
    {
        return [
            'module' => [
                'type' => 'choice',
                'label' => 'Module',
                'required' => false,
                'help' => 'The Zoho CRM module to create records in',
                'choices' => [
                    'Leads' => 'Leads',
                    'Contacts' => 'Contacts',
                    'Accounts' => 'Accounts',
                ],
            ],
        ];
    }

    #[\Override]
    public function sendWebhook(
        CustomFormAnswer $answer,
        array $fieldMapping = [],
        array $extraConfig = []
    ): bool {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Zoho CRM webhook provider is not configured. Set APP_ZOHO_CRM_WEBHOOK_KEY environment variable.');
        }

        $module = $extraConfig['module'] ?? 'Leads';
        $mappedData = $this->mapAnswerData($answer, $fieldMapping);

        // Build record data
        $recordData = [];
        foreach ($mappedData as $key => $value) {
            if (!str_starts_with($key, '_')) {
                $recordData[$key] = $value;
            }
        }

        $payload = [
            'data' => [$recordData],
        ];

        try {
            // Use Zoho CRM v8 API to create records
            $response = $this->httpClient->request('POST', sprintf('https://www.zohoapis.com/crm/v8/%s', $module), [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken '.$this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logSuccess($answer, sprintf('Record sent to Zoho CRM %s module successfully', $module));

                return true;
            }

            $this->logError($answer, sprintf('Zoho CRM API returned status code: %d', $statusCode));

            return false;
        } catch (\Throwable $e) {
            $this->logError($answer, 'Failed to send webhook to Zoho CRM: '.$e->getMessage(), $e);
            throw $e;
        }
    }
}
