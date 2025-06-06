<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Solarium\Core\Client\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @deprecated since 2.6, use Nelmio\SolariumBundle\ClientRegistry instead.
 */
final readonly class ClientRegistry
{
    public function __construct(private ContainerInterface $container)
    {
    }

    /**
     * @deprecated since 2.6, use Nelmio\SolariumBundle\ClientRegistry::getClient() instead.
     */
    public function getClient(): ?Client
    {
        $client = $this->container->get(
            'roadiz_core.solr.client',
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        if (null === $client) {
            return null;
        }
        if (!($client instanceof Client)) {
            throw new \RuntimeException('Solr client must be an instance of '.Client::class);
        }

        return $client;
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
