<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Exception\SolrServerNotAvailableException;
use RZ\Roadiz\Markdown\MarkdownInterface;
use Solarium\Core\Client\Client;
use Solarium\Core\Query\DocumentInterface;
use Solarium\Core\Query\Result\Result;
use Solarium\Core\Query\Result\ResultInterface;
use Solarium\QueryType\Update\Query\Document;
use Solarium\QueryType\Update\Query\Query;

/**
 * @package RZ\Roadiz\CoreBundle\SearchEngine
 */
abstract class AbstractSolarium
{
    public const DOCUMENT_TYPE = 'AbstractDocument';
    public const IDENTIFIER_KEY = 'abstract_id_i';
    public const TYPE_DISCRIMINATOR = 'document_type_s';

    public static array $availableLocalizedTextFields = [
        'en',
        'ar',
        'bg',
        'ca',
        'cz',
        'da',
        'de',
        'el',
        'es',
        'eu',
        'fa',
        'fi',
        'fr',
        'ga',
        'gl',
        'hi',
        'hu',
        'hy',
        'id',
        'it',
        'ja',
        'lv',
        'nl',
        'no',
        'pt',
        'ro',
        'ru',
        'sv',
        'th',
        'tr',
    ];

    protected ClientRegistry $clientRegistry;
    protected bool $indexed = false;
    protected ?DocumentInterface $document = null;
    protected LoggerInterface $logger;
    protected MarkdownInterface $markdown;

    public function __construct(
        ClientRegistry $clientRegistry,
        LoggerInterface $searchEngineLogger,
        MarkdownInterface $markdown
    ) {
        $this->logger = $searchEngineLogger;
        $this->markdown = $markdown;
        $this->clientRegistry = $clientRegistry;
    }

    public function getSolr(): Client
    {
        $solr = $this->clientRegistry->getClient();
        if (null === $solr) {
            throw new SolrServerNotAvailableException();
        }
        return $solr;
    }

    /**
     * Index current nodeSource and commit after.
     *
     * Use this method only when you need to index single NodeSources.
     *
     * @return ResultInterface|null
     * @throws \Exception
     */
    public function indexAndCommit(): ?ResultInterface
    {
        $update = $this->getSolr()->createUpdate();
        $this->createEmptyDocument($update);

        if (true === $this->index()) {
            // add the documents and a commit command to the update query
            $update->addDocument($this->getDocument());
            $update->addCommit();

            return $this->getSolr()->update($update);
        }

        return null;
    }

    /**
     * Update current nodeSource document and commit after.
     *
     * Use this method **only** when you need to re-index a single NodeSources.
     *
     * @return ResultInterface|null
     * @throws \Exception
     */
    public function updateAndCommit(): ?ResultInterface
    {
        $update = $this->getSolr()->createUpdate();
        $this->update($update);
        $update->addCommit(true, true, false);

        $this->logger->debug('[Solr] Document updated.');
        return $this->getSolr()->update($update);
    }

    /**
     * Update current nodeSource document with existing update.
     *
     * Use this method only when you need to re-index bulk NodeSources.
     *
     * @param Query $update
     *
     * @throws \Exception
     */
    public function update(Query $update): void
    {
        $this->clean($update);
        $this->createEmptyDocument($update);
        $this->index();
        // add the document to the update query
        $update->addDocument($this->document);
    }

    /**
     * Remove current document from SearchEngine index.
     *
     * @param Query $update
     * @return bool
     * @throws \RuntimeException If no document is available.
     */
    public function remove(Query $update): bool
    {
        if (null !== $this->document && isset($this->document->id)) {
            $update->addDeleteById($this->document->id);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Remove current Solr document and commit after.
     *
     * Use this method only when you need to remove a single NodeSources.
     */
    public function removeAndCommit(): void
    {
        $update = $this->getSolr()->createUpdate();

        if (true === $this->remove($update)) {
            $update->addCommit(true, true, false);
            $this->getSolr()->update($update);
        }
    }
    /**
     * Remove any document linked to current node-source and commit after.
     *
     * Use this method only when you need to remove a single NodeSources.
     */
    public function cleanAndCommit(): void
    {
        $update = $this->getSolr()->createUpdate();

        if (true === $this->clean($update)) {
            $update->addCommit(true, true, false);
            $this->getSolr()->update($update);
        }
    }

    /**
     * Index current document with entity data.
     *
     * @return bool
     * @throws \Exception
     */
    public function index(): bool
    {
        if ($this->document instanceof Document) {
            $this->document->setKey('id', uniqid('', true));

            try {
                foreach ($this->getFieldsAssoc() as $key => $value) {
                    if (!\is_array($value) || \count($value) > 0) {
                        $this->document->setField($key, $value);
                    }
                }
                return true;
            } catch (\RuntimeException $e) {
                return false;
            }
        }
        throw new \RuntimeException("No Solr item available for current entity", 1);
    }

    /**
     * @return DocumentInterface|null
     */
    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $document
     * @return $this
     * @deprecated Use createEmptyDocument instead of set an empty Solr document.
     */
    public function setDocument(DocumentInterface $document): self
    {
        $this->document = $document;
        return $this;
    }

    /**
     * @param Query $update
     * @return $this
     */
    public function createEmptyDocument(Query $update): self
    {
        $this->document = $update->createDocument();
        return $this;
    }

    abstract public function clean(Query $update): bool;


    /**
     * @return int|string
     */
    abstract public function getDocumentId(): int|string;

    /**
     * Get document from Solr index.
     *
     * @return bool *FALSE* if no document found linked to current node-source.
     */
    public function getDocumentFromIndex(): bool
    {
        $query = $this->getSolr()->createSelect();
        $query->setQuery(static::IDENTIFIER_KEY . ':' . $this->getDocumentId());
        $query->createFilterQuery('type')->setQuery(static::TYPE_DISCRIMINATOR . ':' . static::DOCUMENT_TYPE);

        // this executes the query and returns the result
        $resultset = $this->getSolr()->select($query);

        if (0 === $resultset->getNumFound()) {
            return false;
        } else {
            foreach ($resultset as $document) {
                $this->document = $document;
                return true;
            }
        }
        return false;
    }

    /**
     * Get a key/value array representation of current indexed object.
     *
     * @return array
     * @throws \Exception
     */
    abstract protected function getFieldsAssoc(): array;

    /**
     * @param string|null $content
     * @param bool $stripMarkdown
     *
     * @return string|null
     */
    public function cleanTextContent(?string $content, bool $stripMarkdown = true): ?string
    {
        if (!is_string($content)) {
            return null;
        }
        /*
         * Strip Markdown syntax
         */
        if (true === $stripMarkdown) {
            $content = $this->markdown->textExtra($content);
            // replace BR with space to avoid merged words.
            $content = str_replace(['<br>', '<br />', '<br/>'], ' ', $content);
            $content = strip_tags($content);
        }
        /*
         * Remove ctrl characters
         */
        $content = preg_replace("[:cntrl:]", "", $content);
        $content = preg_replace('/[\x00-\x1F]/', '', $content);
        return $content;
    }
}
