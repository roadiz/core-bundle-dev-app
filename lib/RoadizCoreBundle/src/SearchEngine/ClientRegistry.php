<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Solarium\Core\Client\Client;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class ClientRegistry
{
    protected ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

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
            throw new \RuntimeException('Solr client must be an instance of ' . Client::class);
        }
        return $client;
    }

    public function isClientReady(?Client $client): bool
    {
        if (null === $client) {
            return false;
        }
        $ping = $client->createPing();
        try {
            $client->ping($ping);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
