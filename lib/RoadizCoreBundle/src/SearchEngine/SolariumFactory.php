<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\Handlers\HandlerFactoryInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SolariumFactory implements SolariumFactoryInterface
{
    protected LoggerInterface $logger;

    public function __construct(
        protected readonly ClientRegistry $clientRegistry,
        readonly LoggerInterface $searchEngineLogger,
        protected readonly MarkdownInterface $markdown,
        protected readonly EventDispatcherInterface $dispatcher,
        protected readonly HandlerFactoryInterface $handlerFactory,
    ) {
        $this->logger = $searchEngineLogger;
    }

    public function createWithDocument(Document $document): SolariumDocument
    {
        return new SolariumDocument(
            $document,
            $this,
            $this->clientRegistry,
            $this->logger,
            $this->markdown
        );
    }

    public function createWithDocumentTranslation(DocumentTranslation $documentTranslation): SolariumDocumentTranslation
    {
        return new SolariumDocumentTranslation(
            $documentTranslation,
            $this->clientRegistry,
            $this->dispatcher,
            $this->logger,
            $this->markdown
        );
    }

    public function createWithNodesSources(NodesSources $nodeSource): SolariumNodeSource
    {
        return new SolariumNodeSource(
            $nodeSource,
            $this->clientRegistry,
            $this->dispatcher,
            $this->logger,
            $this->markdown
        );
    }
}
