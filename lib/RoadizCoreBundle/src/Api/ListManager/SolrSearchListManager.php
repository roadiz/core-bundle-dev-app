<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\ListManager;

use RZ\Roadiz\CoreBundle\ListManager\AbstractEntityListManager;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchResultsInterface;
use Symfony\Component\HttpFoundation\Request;

final class SolrSearchListManager extends AbstractEntityListManager
{
    protected SearchHandlerInterface $searchHandler;
    protected ?SearchResultsInterface $searchResults;
    private array $criteria;
    private bool $searchInTags;
    private ?string $query = null;

    public function __construct(
        ?Request $request,
        SearchHandlerInterface $searchHandler,
        array $criteria = [],
        bool $searchInTags = true
    ) {
        parent::__construct($request);
        $this->searchHandler = $searchHandler;
        $this->criteria = $criteria;
        $this->searchInTags = $searchInTags;
    }

    public function handle(bool $disabled = false)
    {
        if ($this->request === null) {
            throw new \InvalidArgumentException('Cannot handle a NULL request.');
        }

        $this->handleRequestQuery($disabled);

        if (null === $this->query) {
            throw new \InvalidArgumentException('Cannot handle a NULL query.');
        }
        /*
         * Query must be longer than 3 chars or Solr might crash
         * on highlighting fields.
         */
        if (\mb_strlen($this->query) > 3) {
            $this->searchResults = $this->searchHandler->searchWithHighlight(
                $this->query, # Use ?q query parameter to search with
                $this->criteria, # a simple criteria array to filter search results
                $this->getItemPerPage(), # result count
                $this->searchInTags, # Search in tags too,
                1,
                $this->getPage()
            );
        } else {
            $this->searchResults = $this->searchHandler->search(
                $this->query, # Use ?q query parameter to search with
                $this->criteria, # a simple criteria array to filter search results
                $this->getItemPerPage(), # result count
                $this->searchInTags, # Search in tags too,
                2,
                $this->getPage()
            );
        }
    }

    protected function handleSearchParam(string $search): void
    {
        parent::handleSearchParam($search);
        $this->query = trim($search);
    }

    /**
     * @inheritDoc
     */
    public function getItemCount(): int
    {
        if (null !== $this->searchResults) {
            return $this->searchResults->getResultCount();
        }
        throw new \InvalidArgumentException('Call EntityListManagerInterface::handle before counting entities.');
    }

    /**
     * @inheritDoc
     */
    public function getEntities(): array
    {
        if (null !== $this->searchResults) {
            return $this->searchResults->getResultItems();
        }
        throw new \InvalidArgumentException('Call EntityListManagerInterface::handle before getting entities.');
    }
}
