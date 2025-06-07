<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Solarium\Core\Client\Client;

/**
 * Null client registry implementation.
 * This class is used when no Solr client is configured.
 */
final readonly class NullClientRegistry implements ClientRegistryInterface
{
    #[\Override]
    public function getClient(?string $clientName = null): ?Client
    {
        return null;
    }
}
