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

        $query = trim($this->request->query->get('search') ?? '');

        if (
            $this->request->query->has('page') &&
            $this->request->query->get('page') > 1
        ) {
            $this->setPage((int) $this->request->query->get('page'));
        } else {
            $this->setPage(1);
        }

        if (
            $this->request->query->has('itemsPerPage') &&
            $this->request->query->get('itemsPerPage') > 0
        ) {
            $this->setItemPerPage((int) $this->request->query->get('itemsPerPage'));
        }

        /*
         * Query must be longer than 3 chars or Solr might crash
         * on highlighting fields.
         */
        if (\mb_strlen($query) > 3) {
            $this->searchResults = $this->searchHandler->searchWithHighlight(
                $query, # Use ?q query parameter to search with
                $this->criteria, # a simple criteria array to filter search results
                $this->getItemPerPage(), # result count
                $this->searchInTags, # Search in tags too,
                1,
                $this->getPage()
            );
        } else {
            $this->searchResults = $this->searchHandler->search(
                $query, # Use ?q query parameter to search with
                $this->criteria, # a simple criteria array to filter search results
                $this->getItemPerPage(), # result count
                $this->searchInTags, # Search in tags too,
                2,
                $this->getPage()
            );
        }
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
    public function getEntities()
    {
        if (null !== $this->searchResults) {
            return $this->searchResults->getResultItems();
        }
        throw new \InvalidArgumentException('Call EntityListManagerInterface::handle before getting entities.');
    }
}
