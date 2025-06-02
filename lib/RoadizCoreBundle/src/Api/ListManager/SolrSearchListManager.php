<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\ListManager;

use RZ\Roadiz\CoreBundle\ListManager\AbstractEntityListManager;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchHandlerInterface;
use RZ\Roadiz\CoreBundle\SearchEngine\SearchResultsInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[Exclude]
final class SolrSearchListManager extends AbstractEntityListManager
{
    private ?SearchResultsInterface $searchResults = null;
    private ?string $query = null;

    public function __construct(
        ?Request $request,
        private readonly SearchHandlerInterface $searchHandler,
        private readonly array $criteria = [],
        private readonly bool $searchInTags = true,
    ) {
        parent::__construct($request);
    }

    #[\Override]
    public function handle(bool $disabled = false): void
    {
        if (null === $this->request) {
            throw new \InvalidArgumentException('Cannot handle a NULL request.');
        }

        $this->handleRequestQuery($disabled);

        if (null === $this->query) {
            throw new BadRequestHttpException('Search param is required.');
        }
        /*
         * Query must be longer than 3 chars or Solr might crash
         * on highlighting fields.
         */
        if (\mb_strlen($this->query) > 3) {
            $this->searchResults = $this->searchHandler->searchWithHighlight(
                $this->query, // Use ?q query parameter to search with
                $this->criteria, // a simple criteria array to filter search results
                $this->getItemPerPage(), // result count
                $this->searchInTags, // Search in tags too,
                $this->getPage()
            );
        } else {
            $this->searchResults = $this->searchHandler->search(
                $this->query, // Use ?q query parameter to search with
                $this->criteria, // a simple criteria array to filter search results
                $this->getItemPerPage(), // result count
                $this->searchInTags, // Search in tags too,
                $this->getPage()
            );
        }
    }

    #[\Override]
    protected function handleSearchParam(string $search): void
    {
        parent::handleSearchParam($search);
        $this->query = trim($search);
    }

    #[\Override]
    public function getItemCount(): int
    {
        if (null !== $this->searchResults) {
            return $this->searchResults->getResultCount();
        }
        throw new \InvalidArgumentException('Call EntityListManagerInterface::handle before counting entities.');
    }

    #[\Override]
    public function getEntities(): array
    {
        if (null !== $this->searchResults) {
            return $this->searchResults->getResultItems();
        }
        throw new \InvalidArgumentException('Call EntityListManagerInterface::handle before getting entities.');
    }
}
