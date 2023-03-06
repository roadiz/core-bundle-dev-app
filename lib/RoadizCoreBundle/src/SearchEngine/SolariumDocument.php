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
 * Wrap a Solarium and a Document’ translations together to ease indexing.
 *
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
class SolariumDocument extends AbstractSolarium
{
    protected array $documentTranslationItems;

    /**
     * @param Document $rzDocument
     * @param SolariumFactoryInterface $solariumFactory
     * @param ClientRegistry $clientRegistry
     * @param LoggerInterface $searchEngineLogger
     * @param MarkdownInterface $markdown
     */
    public function __construct(
        Document $rzDocument,
        SolariumFactoryInterface $solariumFactory,
        ClientRegistry $clientRegistry,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown
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
    public function getDocument(): ?DocumentInterface
    {
        throw new \RuntimeException('Method getDocument cannot be called for SolariumDocument.');
    }

    /**
     * @return array<\Solarium\QueryType\Update\Query\Document|DocumentInterface> Each document translation Solr document
     */
    public function getDocuments()
    {
        $documents = [];
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documents[] = $documentTranslationItem->getDocument();
        }

        return array_filter($documents);
    }

    public function getDocumentId(): string|int
    {
        throw new \RuntimeException('SolariumDocument should not provide any ID');
    }

    /**
     * Get document from Solr index.
     *
     * @return bool *FALSE* if no document found linked to current Roadiz document.
     */
    public function getDocumentFromIndex(): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->getDocumentFromIndex();
        }

        return true;
    }

    /**
     * @param Query $update
     * @return $this
     */
    public function createEmptyDocument(Query $update): self
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->createEmptyDocument($update);
        }
        return $this;
    }

    protected function getFieldsAssoc(): array
    {
        return [];
    }

    /**
     * @param Query $update
     *
     * @return bool
     */
    public function clean(Query $update): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->clean($update);
        }

        return true;
    }

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
     * @return ResultInterface|null
     * @throws \Exception
     */
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
     * @param Query $update
     *
     * @throws \Exception
     */
    public function update(Query $update): void
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->update($update);
        }
    }

    /**
     * @param Query $update
     *
     * @return bool
     */
    public function remove(Query $update): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->remove($update);
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function removeAndCommit(): void
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->removeAndCommit();
        }
    }

    /**
     * @inheritdoc
     */
    public function cleanAndCommit(): void
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->cleanAndCommit();
        }
    }

    /**
     * @inheritdoc
     */
    public function index(): bool
    {
        /** @var SolariumDocumentTranslation $documentTranslationItem */
        foreach ($this->documentTranslationItems as $documentTranslationItem) {
            $documentTranslationItem->index();
        }

        return true;
    }
}
