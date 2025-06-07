<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Solarium\Core\Client\Client;

interface ClientRegistryInterface
{
    public function getClient(?string $clientName = null): ?Client;
}
