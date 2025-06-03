<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\QueryType\Update\Query\Query;

/**
 * Wrap a Solarium and a document translations together to ease indexing.
 */
class SolariumDocument extends AbstractSolarium
{
    protected array $documentTranslationItems;

    public function __construct(
        readonly Document $rzDocument,
        SolariumFactoryInterface $solariumFactory,
        ClientRegistry $clientRegistry,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown,
    ) {
        parent::__construct($clientRegistry, $searchEngineLogger, $markdown);
        $this->documentTranslationItems = [];

        foreach ($rzDocument->getDocumentTranslations() as $documentTranslation) {
            $this->documentTranslationItems[] = $solariumFactory->createWithDocumentTranslation($documentTranslation);
        }
    }

    /**
     * @deprecated
     */
    #[\Override]
    public function getDocument(): ?DocumentInterface
    {
        throw new \RuntimeException('Method getDocument cannot be called for SolariumDocument.');
    }

    /**
     * @return array<\Solarium\QueryType\Update\Query\Document|DocumentInterface> Each document translation Solr document
     */
    public function getDocuments(): array
    {
        $documents = [];
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documents[] = $documentTranslationItem->getDocument();
        }

        return array_filter($documents);
    }

    #[\Override]
    public function getDocumentId(): string|int
    {
        throw new \RuntimeException('SolariumDocument should not provide any ID');
    }

    /**
     * Get document from Solr index.
     *
     * @return bool *FALSE* if no document found linked to current Roadiz document
     */
    #[\Override]
    public function getDocumentFromIndex(): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->getDocumentFromIndex();
        }

        return true;
    }

    /**
     * @return $this
     */
    #[\Override]
    public function createEmptyDocument(Query $update): self
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->createEmptyDocument($update);
        }

        return $this;
    }

    #[\Override]
    protected function getFieldsAssoc(): array
    {
        return [];
    }

    #[\Override]
    public function clean(Query $update): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->clean($update);
        }

        return true;
    }

    #[\Override]
    public function indexAndCommit(): ?ResultInterface
    {
        $lastResult = null;
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $lastResult = $documentTranslationItem->indexAndCommit();
        }

        return $lastResult;
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function updateAndCommit(): ?ResultInterface
    {
        $lastResult = null;
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $lastResult = $documentTranslationItem->updateAndCommit();
        }

        return $lastResult;
    }

    /**
     * @throws \Exception
     */
    #[\Override]
    public function update(Query $update): void
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->update($update);
        }
    }

    #[\Override]
    public function remove(Query $update): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->remove($update);
        }

        return true;
    }

    #[\Override]
    public function removeAndCommit(): void
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->removeAndCommit();
        }
    }

    #[\Override]
    public function cleanAndCommit(): void
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->cleanAndCommit();
        }
    }

    #[\Override]
    public function index(): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->index();
        }

        return true;
    }

    #[\Override]
    protected function getIdempotentIdentifier(): string
    {
        throw new \InvalidArgumentException('SolariumDocument should not provide any ID');
    }
}
