<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Solarium\Core\Client\Client;

/**
 * @deprecated since 2.6, use Nelmio\SolariumBundle\ClientRegistry instead.
 */
final readonly class ClientRegistry
{
    public function __construct(private \Nelmio\SolariumBundle\ClientRegistry $decoratedClientRegistry)
    {
    }

    /**
     * @deprecated since 2.6, use Nelmio\SolariumBundle\ClientRegistry::getClient() instead.
     */
    public function getClient(): ?Client
    {
        return $this->decoratedClientRegistry->getClient();
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
