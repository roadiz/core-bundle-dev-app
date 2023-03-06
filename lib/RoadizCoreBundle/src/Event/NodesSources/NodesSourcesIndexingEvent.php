<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Event\NodesSources;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\SearchEngine\AbstractSolarium;
use Symfony\Contracts\EventDispatcher\Event;

final class NodesSourcesIndexingEvent extends Event
{
    protected NodesSources $nodeSource;
    protected array $associations;
    protected AbstractSolarium $solariumDocument;
    protected bool $subResource;

    /**
     * @param NodesSources     $nodeSource
     * @param array            $associations
     * @param AbstractSolarium $solariumDocument
     * @param bool             $subResource
     */
    public function __construct(
        NodesSources $nodeSource,
        array $associations,
        AbstractSolarium $solariumDocument,
        bool $subResource = false
    ) {
        $this->nodeSource = $nodeSource;
        $this->associations = $associations;
        $this->solariumDocument = $solariumDocument;
        $this->subResource = $subResource;
    }

    public function getNodeSource(): NodesSources
    {
        return $this->nodeSource;
    }

    /**
     * Get Solr document data to index.
     *
     * @return array
     */
    public function getAssociations(): array
    {
        return $this->associations;
    }

    /**
     * Set Solr document data to index.
     *
     * @param array $associations
     * @return NodesSourcesIndexingEvent
     */
    public function setAssociations(array $associations): NodesSourcesIndexingEvent
    {
        $this->associations = $associations;
        return $this;
    }

    /**
     * @return AbstractSolarium
     */
    public function getSolariumDocument(): AbstractSolarium
    {
        return $this->solariumDocument;
    }

    /**
     * @param AbstractSolarium $solariumDocument
     *
     * @return NodesSourcesIndexingEvent
     */
    public function setSolariumDocument(AbstractSolarium $solariumDocument): NodesSourcesIndexingEvent
    {
        $this->solariumDocument = $solariumDocument;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSubResource(): bool
    {
        return $this->subResource;
    }
}
