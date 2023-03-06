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
    protected ClientRegistry $clientRegistry;
    protected LoggerInterface $logger;
    protected MarkdownInterface $markdown;
    protected EventDispatcherInterface $dispatcher;
    protected HandlerFactoryInterface $handlerFactory;

    public function __construct(
        ClientRegistry $clientRegistry,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown,
        EventDispatcherInterface $dispatcher,
        HandlerFactoryInterface $handlerFactory
    ) {
        $this->clientRegistry = $clientRegistry;
        $this->logger = $searchEngineLogger;
        $this->markdown = $markdown;
        $this->dispatcher = $dispatcher;
        $this->handlerFactory = $handlerFactory;
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
