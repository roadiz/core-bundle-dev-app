<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Message;

/**
 * Message to dispatch CustomForm webhook to external system asynchronously.
 */
final readonly class CustomFormWebhookMessage
{
    /**
     * @param array<string, string> $fieldMapping
     * @param array<string, mixed>  $extraConfig
     */
    public function __construct(
        private int $customFormAnswerId,
        private string $providerName,
        private array $fieldMapping,
        private array $extraConfig,
    ) {
    }

    public function getCustomFormAnswerId(): int
    {
        return $this->customFormAnswerId;
    }

    public function getProviderName(): string
    {
        return $this->providerName;
    }

    /**
     * @return array<string, string>
     */
    public function getFieldMapping(): array
    {
        return $this->fieldMapping;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtraConfig(): array
    {
        return $this->extraConfig;
    }
}
