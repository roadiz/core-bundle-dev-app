<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\SearchEngine;

use Doctrine\Persistence\ObjectManager;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * Wrapper over Solr search results and metas.
 */
class SolrSearchResults implements SearchResultsInterface
{
    #[Ignore]
    protected int $position;

    /**
     * @var array<SolrSearchResultItem>|null
     */
    #[Ignore]
    protected ?array $resultItems;

    public function __construct(
        #[Ignore]
        protected readonly array $response,
        #[Ignore]
        protected readonly ObjectManager $entityManager,
    ) {
        $this->position = 0;
        $this->resultItems = null;
    }

    public function getResultCount(): int
    {
        if (
            isset($this->response['response']['numFound'])
        ) {
            return (int) $this->response['response']['numFound'];
        }

        return 0;
    }

    /**
     * @return array<SolrSearchResultItem>
     */
    public function getResultItems(): array
    {
        if (null === $this->resultItems) {
            $this->resultItems = [];
            if (
                isset($this->response['response']['docs'])
            ) {
                $this->resultItems = array_filter(array_map(
                    function (array $item) {
                        $object = $this->getHydratedItem($item);
                        if (!\is_object($object)) {
                            return null;
                        }
                        $highlighting = $this->getHighlighting($item['id']);

                        return new SolrSearchResultItem(
                            $object,
                            $highlighting
                        );
                    },
                    $this->response['response']['docs']
                ));
            }
        }

        return $this->resultItems;
    }

    /**
     * Get highlighting for one field.
     * This does not merge highlighting for all fields anymore.
     *
     * @return array<string, array>
     */
    protected function getHighlighting(string $id): array
    {
        if (isset($this->response['highlighting'][$id]) && \is_array($this->response['highlighting'][$id])) {
            return $this->response['highlighting'][$id];
        }

        return [];
    }

    public function map(callable $callable): array
    {
        return array_map($callable, $this->getResultItems());
    }

    /**
     * @return array|object|null
     */
    protected function getHydratedItem(array $item): mixed
    {
        if (isset($item[AbstractSolarium::TYPE_DISCRIMINATOR])) {
            switch ($item[AbstractSolarium::TYPE_DISCRIMINATOR]) {
                case SolariumNodeSource::DOCUMENT_TYPE:
                    return $this->entityManager->find(
                        NodesSources::class,
                        $item[SolariumNodeSource::IDENTIFIER_KEY]
                    );
                case SolariumDocumentTranslation::DOCUMENT_TYPE:
                    $documentTranslation = $this->entityManager->find(
                        DocumentTranslation::class,
                        $item[SolariumDocumentTranslation::IDENTIFIER_KEY]
                    );

                    return $documentTranslation?->getDocument();
            }
        }

        return $item;
    }

    /**
     * Return the current element.
     *
     * @see https://php.net/manual/en/iterator.current.php
     * @since 5.0
     */
    #[\ReturnTypeWillChange]
    public function current(): SolrSearchResultItem
    {
        return $this->getResultItems()[$this->position];
    }

    /**
     * Move forward to next element.
     *
     * @see https://php.net/manual/en/iterator.next.php
     *
     * @return void any returned value is ignored
     *
     * @since 5.0
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element.
     *
     * @see https://php.net/manual/en/iterator.key.php
     * @since 5.0
     */
    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid.
     *
     * @see https://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     *              Returns true on success or false on failure.
     *
     * @since 5.0
     */
    public function valid(): bool
    {
        return isset($this->getResultItems()[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see https://php.net/manual/en/iterator.rewind.php
     *
     * @return void any returned value is ignored
     *
     * @since 5.0
     */
    public function rewind(): void
    {
        $this->position = 0;
    }
}
