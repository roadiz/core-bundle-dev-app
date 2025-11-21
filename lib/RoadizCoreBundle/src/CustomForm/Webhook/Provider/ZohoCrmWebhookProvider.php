<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Zoho CRM webhook provider.
 *
 * https://www.zoho.com/crm/developer/docs/api/v8/scopes.html
 */
final readonly class ZohoCrmWebhookProvider extends AbstractCustomFormWebhookProvider
{
    /**
     * @param string|null $accountUrl   https://accounts.zoho.com/oauth/serverinfo
     * @param string|null $soId         this parameter is derived from the unique ServiceOrg or organization or portal ID (zsoid)
     * @param string|null $clientId     client ID (consumer key) that you obtained during client registration
     * @param string|null $clientSecret client secret that you obtained during client registration
     */
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        private ?string $accountUrl = 'https://accounts.zoho.eu',
        private ?string $soId = null,
        private ?string $clientId = null,
        #[\SensitiveParameter]
        private ?string $clientSecret = null,
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
        return !empty($this->soId) && !empty($this->accountUrl) && !empty($this->clientId) && !empty($this->clientSecret);
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
        array $extraConfig = [],
    ): bool {
        if (!$this->isConfigured()) {
            throw new \RuntimeException('Zoho CRM webhook provider is not configured. Set APP_ZOHO_CRM_WEBHOOK_KEY environment variable.');
        }

        $module = $extraConfig['module'] ?? 'Leads';

        if (!is_string($module) || empty($module)) {
            throw new \InvalidArgumentException('module is required in extraConfig for Zoho CRM provider');
        }

        $mappedData = $this->mapAnswerData($answer, $fieldMapping);

        // Build record data
        $recordData = array_filter($mappedData, fn ($key) => !str_starts_with((string) $key, '_'), ARRAY_FILTER_USE_KEY);

        $payload = [
            'data' => [$recordData],
        ];

        try {
            $oauth2Response = $this->httpClient->request('POST', $this->accountUrl.'/oauth/v2/token', [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'query' => [
                    'soid' => $this->soId,
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                    'scope' => 'ZohoCRM.modules.'.$module.'.CREATE',
                ],
            ]);

            $oauth2StatusCode = $oauth2Response->getStatusCode();
            if ($oauth2StatusCode >= 300) {
                $this->logError($answer, sprintf('Cannot authenticate to Zoho CRM %s module.', $module));

                return false;
            }
            /**
             * {
             * "access_token": "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
             * "scope": "ZohoCRM.modules.leads.CREATE",
             * "api_domain": "https://www.zohoapis.eu",
             * "token_type": "Bearer",
             * "expires_in": 3600
             * }.
             */
            $oauth2Data = \json_decode($oauth2Response->getContent(), true, flags: JSON_THROW_ON_ERROR);

            // Use Zoho CRM v8 API to create records
            $response = $this->httpClient->request('POST', sprintf('%s/crm/v8/%s', $oauth2Data['api_domain'], $module), [
                'headers' => [
                    'Authorization' => 'Zoho-oauthtoken '.$oauth2Data['access_token'],
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
