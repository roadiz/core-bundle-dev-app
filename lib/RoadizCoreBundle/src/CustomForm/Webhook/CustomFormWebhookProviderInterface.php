<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook;

use RZ\Roadiz\CoreBundle\Entity\CustomFormAnswer;

/**
 * Interface for webhook providers that send CustomForm answers to external systems.
 */
interface CustomFormWebhookProviderInterface
{
    /**
     * Get the unique name/identifier of the provider.
     */
    public function getName(): string;

    /**
     * Get the human-readable display name of the provider.
     */
    public function getDisplayName(): string;

    /**
     * Check if the provider is properly configured with credentials.
     */
    public function isConfigured(): bool;

    /**
     * Send the CustomFormAnswer data to the external system.
     *
     * @param CustomFormAnswer      $answer       The form answer to send
     * @param array<string, string> $fieldMapping Map of CustomForm field names to provider field names
     * @param array<string, mixed>  $extraConfig  Additional provider-specific configuration
     *
     * @return bool True if the webhook was sent successfully
     *
     * @throws \Exception If the webhook fails to send
     */
    public function sendWebhook(
        CustomFormAnswer $answer,
        array $fieldMapping = [],
        array $extraConfig = [],
    ): bool;

    /**
     * Get the configuration schema for this provider.
     * Returns an array describing the additional configuration fields needed.
     *
     * @return array<string, array{type: string, label: string, required: bool, help?: string}>
     */
    public function getConfigSchema(): array;
}
