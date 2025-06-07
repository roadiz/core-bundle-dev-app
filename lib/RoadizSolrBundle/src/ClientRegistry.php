<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Solarium\Core\Client\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ClientRegistry implements ClientRegistryInterface
{
    public function __construct(
        #[Autowire(service: 'solarium.client_registry')]
        private \Nelmio\SolariumBundle\ClientRegistry $decoratedClientRegistry,
    ) {
    }

    #[\Override]
    public function getClient(?string $clientName = null): Client
    {
        return $this->decoratedClientRegistry->getClient($clientName);
    }

    /**
     * @deprecated since 2.6, no replacement will be provided.
     */
    public function isClientReady(?Client $client): bool
    {
        if (null === $client) {
            return false;
        }
        $ping = $client->createPing();
        try {
            $client->ping($ping);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
