<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Brevo (formerly Sendinblue) webhook provider.
 */
final readonly class BrevoWebhookProvider extends AbstractCustomFormWebhookProvider
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
        return 'brevo';
    }

    #[\Override]
    public function getDisplayName(): string
    {
        return 'Brevo (Sendinblue)';
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
            'list_id' => [
                'type' => 'text',
                'label' => 'List ID',
                'required' => true,
                'help' => 'The Brevo contact list ID to add contacts to',
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
            throw new \RuntimeException('Brevo webhook provider is not configured. Set APP_BREVO_WEBHOOK_KEY environment variable.');
        }

        if (empty($extraConfig['list_id'])) {
            throw new \InvalidArgumentException('list_id is required in extraConfig for Brevo provider');
        }

        $mappedData = $this->mapAnswerData($answer, $fieldMapping);

        // Extract email from mapped data or answer
        $email = $mappedData['email'] ?? $mappedData['EMAIL'] ?? $answer->getEmail();
        if (!$email) {
            throw new \InvalidArgumentException('Email is required to send to Brevo');
        }

        // Build contact attributes
        $attributes = [];
        foreach ($mappedData as $key => $value) {
            if ('email' !== $key && !str_starts_with($key, '_')) {
                $attributes[strtoupper($key)] = $value;
            }
        }

        $payload = [
            'email' => $email,
            'attributes' => $attributes,
            'listIds' => [(int) $extraConfig['list_id']],
            'updateEnabled' => true,
        ];

        $eventPayload = [
            'identifiers' => [
                'email_id' => $email,
            ],
            'contact_properties' => $attributes,
            'event_properties' => [
                ...$attributes,
                'custom_form_id' => $answer->getCustomForm()->getId(),
                'custom_form_name' => $answer->getCustomForm()->getName(),
            ],
            'event_name' => 'custom_form_submission',
        ];

        try {
            /*
             * First create or update contact in Brevo
             */
            $response = $this->httpClient->request('POST', 'https://api.brevo.com/v3/contacts', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'accept' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logSuccess($answer, 'Contact sent to Brevo successfully');

                /*
                 * Then create event for that contact
                 */
                $eventResponse = $this->httpClient->request('POST', 'https://api.brevo.com/v3/events', [
                    'headers' => [
                        'api-key' => $this->apiKey,
                        'Content-Type' => 'application/json',
                        'accept' => 'application/json',
                    ],
                    'json' => $eventPayload,
                ]);

                $eventStatusCode = $eventResponse->getStatusCode();
                if ($eventStatusCode >= 200 && $eventStatusCode < 300) {
                    $this->logSuccess($answer, 'Event created for contact on Brevo successfully');

                    return true;
                }

                $this->logError($answer, sprintf('Brevo Event API returned status code: %d', $eventStatusCode));

                return false;
            }

            $this->logError($answer, sprintf('Brevo Contact API returned status code: %d', $statusCode));

            return false;
        } catch (\Throwable $e) {
            $this->logError($answer, 'Failed to send webhook to Brevo: '.$e->getMessage(), $e);
            throw $e;
        }
    }
}
