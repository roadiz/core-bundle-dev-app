<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle\Solarium;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\Markdown\MarkdownInterface;
use RZ\Roadiz\SolrBundle\ClientRegistryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SolariumFactory implements SolariumFactoryInterface
{
    public function __construct(
        protected readonly ClientRegistryInterface $clientRegistry,
        protected readonly LoggerInterface $searchEngineLogger,
        protected readonly MarkdownInterface $markdown,
        protected readonly EventDispatcherInterface $dispatcher,
        protected readonly HandlerFactoryInterface $handlerFactory,
    ) {
    }

    #[\Override]
    public function createWithDocument(Document $document): SolariumDocument
    {
        return new SolariumDocument(
            $document,
            $this,
            $this->clientRegistry,
            $this->searchEngineLogger,
            $this->markdown
        );
    }

    #[\Override]
    public function createWithDocumentTranslation(DocumentTranslation $documentTranslation): SolariumDocumentTranslation
    {
        return new SolariumDocumentTranslation(
            $documentTranslation,
            $this->clientRegistry,
            $this->dispatcher,
            $this->searchEngineLogger,
            $this->markdown
        );
    }

    #[\Override]
    public function createWithNodesSources(NodesSources $nodeSource): SolariumNodeSource
    {
        return new SolariumNodeSource(
            $nodeSource,
            $this->clientRegistry,
            $this->dispatcher,
            $this->searchEngineLogger,
            $this->markdown
        );
    }
}
