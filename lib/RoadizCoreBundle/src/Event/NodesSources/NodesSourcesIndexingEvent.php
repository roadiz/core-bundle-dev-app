<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\NodesSources;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use Symfony\Contracts\EventDispatcher\Event;

final class NodesSourcesIndexingEvent extends Event
{
    public function __construct(
        private readonly NodesSources $nodeSource,
        private array $associations,
        private readonly AbstractSolarium $solariumDocument,
        private readonly bool $subResource = false,
    ) {
    }

    public function getNodeSource(): NodesSources
    {
        return $this->nodeSource;
    }

    /**
     * Get Solr document data to index.
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }

    /**
     * Set Solr document data to index.
     */
    public function setAssociations(array $associations): NodesSourcesIndexingEvent
    {
        $this->associations = $associations;

        return $this;
    }

    public function getSolariumDocument(): AbstractSolarium
    {
        return $this->solariumDocument;
    }

    public function isSubResource(): bool
    {
        return $this->subResource;
    }
}
