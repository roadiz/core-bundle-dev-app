# CustomForm Webhook System

This document describes the webhook system for CustomForms, which allows automatic dispatch of form submission data to external CRM systems.

## Overview

When a CustomForm is submitted, the webhook system can automatically send the form data to external services like Brevo, Mailchimp, HubSpot, Zoho CRM, or any custom HTTP endpoint.

## Features

- **Multiple Providers**: Built-in support for popular CRM systems
- **Field Mapping**: Map CustomForm fields to provider-specific fields
- **Async Processing**: Webhooks are dispatched asynchronously via Symfony Messenger
- **Idempotency**: Each webhook dispatch uses the CustomFormAnswer ID to ensure idempotent processing
- **Retry Logic**: Failed webhooks are automatically retried via Symfony Messenger's retry mechanism
- **Extensible**: Easy to add custom webhook providers at project level

## Supported Providers

### Built-in Providers

1. **Brevo (Sendinblue)** - `brevo`
2. **Mailchimp** - `mailchimp`
3. **HubSpot** - `hubspot`
4. **Zoho CRM** - `zoho_crm`
5. **Generic HTTP** - `generic_http`

## Configuration

### 1. Environment Variables

Configure provider credentials in your `.env` file:

```bash
# Brevo (Sendinblue)
APP_BREVO_WEBHOOK_KEY=your-api-key-here

# Mailchimp (format: key-server, e.g., abc123-us1)
APP_MAILCHIMP_WEBHOOK_KEY=your-api-key-here

# HubSpot
APP_HUBSPOT_WEBHOOK_KEY=your-api-key-here

# Zoho CRM
APP_ZOHO_CRM_WEBHOOK_KEY=your-oauth-token-here
```

### 2. CustomForm Configuration

In the Roadiz admin panel:

1. Navigate to **Custom Forms**
2. Edit the desired custom form
3. Scroll to **Webhook Configuration** section
4. Enable webhook and configure:
   - **Enable Webhook**: Check to activate
   - **Webhook Provider**: Select from dropdown (e.g., `Brevo`)
   - **Field Mapping (JSON)**: Map form fields to provider fields
   - **Extra Configuration (JSON)**: Provider-specific settings

#### Field Mapping Example

Map your CustomForm field names to provider field names:

```json
{
  "email": "email",
  "first_name": "FIRSTNAME",
  "last_name": "LASTNAME",
  "company": "COMPANY"
}
```

#### Extra Configuration Examples

**Brevo:**
```json
{
  "list_id": "123"
}
```

**Mailchimp:**
```json
{
  "audience_id": "abc123xyz",
  "status": "subscribed"
}
```

**Generic HTTP:**
```json
{
  "url": "https://your-webhook-endpoint.com/api/webhook",
  "method": "POST",
  "auth_header": "Bearer your-token-here"
}
```

## How It Works

1. User submits a CustomForm
2. `CustomFormAnswerSubmittedEvent` is dispatched
3. `CustomFormWebhookSubscriber` checks if webhooks are enabled
4. If enabled, a `CustomFormWebhookMessage` is dispatched to Symfony Messenger
5. `CustomFormWebhookMessageHandler` processes the message asynchronously:
   - Loads the CustomFormAnswer
   - Gets the configured webhook provider
   - Maps form fields to provider fields
   - Sends the webhook to the external system
6. If the webhook fails, Symfony Messenger will retry based on the retry policy

## Creating Custom Providers

To add a custom webhook provider in your project:

### 1. Create Provider Class

```php
<?php

namespace App\CustomForm\Webhook\Provider;

use RZ\Roadiz\CoreBundle\CustomForm\Webhook\AbstractCustomFormWebhookProvider;
use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;

final class MyCustomProvider extends AbstractCustomFormWebhookProvider
{
    public function __construct(
        HttpClientInterface $httpClient,
        LoggerInterface $logger,
        private readonly ?string $apiKey = null,
    ) {
        parent::__construct($httpClient, $logger);
    }

    public function getName(): string
    {
        return 'my_custom';
    }

    public function getDisplayName(): string
    {
        return 'My Custom CRM';
    }

    public function isConfigured(): bool
    {
        // Check if required configuration is present
        return !empty($this->apiKey);
    }

    public function getConfigSchema(): array
    {
        return [
            'project_id' => [
                'type' => 'text',
                'label' => 'Project ID',
                'required' => true,
                'help' => 'Your project identifier',
            ],
        ];
    }

    public function sendWebhook(
        CustomFormAnswer $answer,
        array $fieldMapping = [],
        array $extraConfig = []
    ): bool {
        $mappedData = $this->mapAnswerData($answer, $fieldMapping);
        
        // Implement your webhook logic here
        try {
            $response = $this->httpClient->request('POST', 'https://api.example.com/webhook', [
                'json' => $mappedData,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                ],
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 200 && $statusCode < 300) {
                $this->logSuccess($answer, 'Webhook sent successfully');
                return true;
            }

            $this->logError($answer, sprintf('API returned status code: %d', $statusCode));
            return false;
        } catch (\Throwable $e) {
            $this->logError($answer, 'Failed to send webhook: ' . $e->getMessage(), $e);
            throw $e;
        }
    }
}
```

### 2. Register as Service

Add to your `config/services.yaml`:

```yaml
services:
    App\CustomForm\Webhook\Provider\MyCustomProvider:
        arguments:
            $apiKey: '%env(APP_CUSTOM_API_KEY)%'
        tags: ['roadiz_core.custom_form_webhook_provider']
```

### 3. Add Environment Variable

Add to `.env`:

```bash
APP_CUSTOM_API_KEY=your-key-here
```

## Security

- **Credentials Storage**: All provider credentials are stored in environment variables, never in the database
- **Access Control**: Webhook configuration requires `ROLE_ACCESS_CUSTOMFORMS_WEBHOOKS` permission
- **HTTPS Only**: Always use HTTPS endpoints for webhook URLs in production

## Troubleshooting

### Webhook Not Being Sent

1. Check that webhooks are enabled for the CustomForm
2. Verify the provider is configured (check environment variables)
3. Check Symfony Messenger logs for errors
4. Verify the provider is registered in the service container

### Provider Configuration Not Working

1. Check environment variable names match exactly
2. Restart PHP-FPM/web server after changing environment variables
3. Check provider's `isConfigured()` method returns true

### Field Mapping Issues

1. Ensure JSON is valid (use a JSON validator)
2. Check field names match exactly (case-sensitive)
3. Verify provider accepts the field names you're mapping to

## Monitoring

Monitor webhook dispatch in your application logs:

```bash
# Search for webhook-related logs
grep "CustomForm webhook" var/log/prod.log

# Check Messenger failed messages
bin/console messenger:failed:show
```

## Testing

To test webhook providers without actually sending data:

1. Use the `GenericHttpWebhookProvider` with a test endpoint like [webhook.site](https://webhook.site)
2. Configure your CustomForm with the test URL
3. Submit the form and check the webhook.site dashboard

Example configuration:
```json
{
  "url": "https://webhook.site/your-unique-id",
  "method": "POST"
}
```
