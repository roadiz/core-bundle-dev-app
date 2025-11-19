<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\CustomForm\Webhook;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Registry for managing CustomForm webhook providers.
 */
final class CustomFormWebhookProviderRegistry
{
    /**
     * @var array<string, CustomFormWebhookProviderInterface>
     */
    private array $providers = [];

    /**
     * @param iterable<CustomFormWebhookProviderInterface> $providers
     */
    public function __construct(
        #[AutowireIterator('roadiz_core.custom_form_webhook_provider')]
        private readonly iterable $providers,
    ) {
        foreach ($this->providers as $provider) {
            $this->addProvider($provider);
        }
    }

    public function addProvider(CustomFormWebhookProviderInterface $provider): void
    {
        $this->providers[$provider->getName()] = $provider;
    }

    public function getProvider(string $name): ?CustomFormWebhookProviderInterface
    {
        return $this->providers[$name] ?? null;
    }

    public function hasProvider(string $name): bool
    {
        return isset($this->providers[$name]);
    }

    /**
     * @return array<string, CustomFormWebhookProviderInterface>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * Get provider choices for forms.
     *
     * @return array<string, string> Display name => provider name
     */
    public function getProviderChoices(): array
    {
        $choices = [];
        foreach ($this->providers as $provider) {
            $choices[$provider->getDisplayName()] = $provider->getName();
        }

        return $choices;
    }
}
