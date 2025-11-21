<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Mailchimp webhook provider.
 */
final readonly class MailchimpWebhookProvider extends AbstractCustomFormWebhookProvider
{
    private ?string $serverPrefix;

    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        #[\SensitiveParameter]
        private ?string $apiKey = null,
    ) {
        parent::__construct($httpClient, $logger);
        // Extract server prefix from API key (format: xxxxx-us1)
        $this->serverPrefix = $this->apiKey ? explode('-', $this->apiKey)[1] ?? null : null;
    }

    #[\Override]
    public function getName(): string
    {
        return 'mailchimp';
    }

    #[\Override]
    public function getDisplayName(): string
    {
        return 'Mailchimp';
    }

    #[\Override]
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->serverPrefix);
    }

    #[\Override]
    public function getConfigSchema(): array
    {
        return [
            'audience_id' => [
                'type' => 'text',
                'label' => 'Audience ID',
                'required' => true,
                'help' => 'The Mailchimp audience (list) ID',
            ],
            'status' => [
                'type' => 'choice',
                'label' => 'Subscription Status',
                'required' => false,
                'help' => 'The subscription status for the contact',
                'choices' => [
                    'Subscribed' => 'subscribed',
                    'Pending' => 'pending',
                    'Unsubscribed' => 'unsubscribed',
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
            throw new \RuntimeException('Mailchimp webhook provider is not configured. Set APP_MAILCHIMP_WEBHOOK_KEY environment variable.');
        }

        if (empty($extraConfig['audience_id'])) {
            throw new \InvalidArgumentException('audience_id is required in extraConfig for Mailchimp provider');
        }

        $mappedData = $this->mapAnswerData($answer, $fieldMapping);

        // Extract email from mapped data or answer
        $email = $mappedData['email'] ?? $answer->getEmail();
        if (!$email || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Valid email is required to send to Mailchimp');
        }

        // Build merge fields
        $mergeFields = [];
        foreach ($mappedData as $key => $value) {
            if ('email' !== $key && !str_starts_with($key, '_')) {
                $mergeFields[strtoupper($key)] = $value;
            }
        }

        $status = $extraConfig['status'] ?? 'subscribed';

        $payload = [
            'email_address' => $email,
            'status' => $status,
            'merge_fields' => $mergeFields,
        ];

        try {
            // Use Mailchimp Marketing API v3 to add/update member
            $url = sprintf(
                'https://%s.api.mailchimp.com/3.0/lists/%s/members',
                $this->serverPrefix,
                $extraConfig['audience_id']
            );

            $response = $this->httpClient->request('POST', $url, [
                'auth_basic' => ['anystring', $this->apiKey],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logSuccess($answer, 'Contact sent to Mailchimp successfully');

                return true;
            }

            $this->logError($answer, sprintf('Mailchimp API returned status code: %d', $statusCode));

            return false;
        } catch (\Throwable $e) {
            $this->logError($answer, 'Failed to send webhook to Mailchimp: '.$e->getMessage(), $e);
            throw $e;
        }
    }
}
