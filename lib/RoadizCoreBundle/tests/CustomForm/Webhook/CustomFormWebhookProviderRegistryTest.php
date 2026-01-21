<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\CustomForm\Webhook;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\CustomFormWebhookProviderRegistry;
use RZ\Roadiz\CoreBundle\CustomForm\Webhook\Provider\GenericHttpWebhookProvider;
use Symfony\Component\HttpClient\MockHttpClient;

class CustomFormWebhookProviderRegistryTest extends TestCase
{
    public function testCanAddAndRetrieveProvider(): void
    {
        $httpClient = new MockHttpClient();
        $logger = new NullLogger();
        $provider = new GenericHttpWebhookProvider($httpClient, $logger);

        $registry = new CustomFormWebhookProviderRegistry([$provider]);

        $this->assertTrue($registry->hasProvider('generic_http'));
        $this->assertSame($provider, $registry->getProvider('generic_http'));
    }

    public function testReturnsNullForNonExistentProvider(): void
    {
        $registry = new CustomFormWebhookProviderRegistry([]);

        $this->assertFalse($registry->hasProvider('non_existent'));
        $this->assertNull($registry->getProvider('non_existent'));
    }

    public function testGetProviderChoices(): void
    {
        $httpClient = new MockHttpClient();
        $logger = new NullLogger();
        $provider = new GenericHttpWebhookProvider($httpClient, $logger);

        $registry = new CustomFormWebhookProviderRegistry([$provider]);
        $choices = $registry->getProviderChoices();

        $this->assertIsArray($choices);
        $this->assertArrayHasKey('Generic HTTP Webhook', $choices);
        $this->assertEquals('generic_http', $choices['Generic HTTP Webhook']);
    }

    public function testGetAllProviders(): void
    {
        $httpClient = new MockHttpClient();
        $logger = new NullLogger();
        $provider = new GenericHttpWebhookProvider($httpClient, $logger);

        $registry = new CustomFormWebhookProviderRegistry([$provider]);
        $providers = $registry->getProviders();

        $this->assertIsArray($providers);
        $this->assertCount(1, $providers);
        $this->assertArrayHasKey('generic_http', $providers);
    }
}
