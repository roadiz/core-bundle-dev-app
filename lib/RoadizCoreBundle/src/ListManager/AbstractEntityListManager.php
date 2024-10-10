<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractEntityListManager implements EntityListManagerInterface
{
    protected bool $pagination = true;
    protected ?array $queryArray = null;
    protected ?int $currentPage = null;
    protected ?int $itemPerPage = null;
    protected ?string $searchPattern = null;
    protected bool $displayNotPublishedNodes;
    protected bool $displayAllNodesStatuses;
    protected bool $allowRequestSorting = true;
    protected bool $allowRequestSearching = true;

    public function __construct(protected readonly ?Request $request = null)
    {
        $this->displayNotPublishedNodes = false;
        $this->displayAllNodesStatuses = false;
        if (null !== $request) {
            $this->queryArray = array_filter($request->query->all());
        } else {
            $this->queryArray = [];
        }
        $this->itemPerPage = static::ITEM_PER_PAGE;
    }

    public function setAllowRequestSorting(bool $allowRequestSorting): self
    {
        $this->allowRequestSorting = $allowRequestSorting;
        return $this;
    }

    public function setAllowRequestSearching(bool $allowRequestSearching): self
    {
        $this->allowRequestSearching = $allowRequestSearching;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayingNotPublishedNodes(): bool
    {
        return $this->displayNotPublishedNodes;
    }

    public function setDisplayingNotPublishedNodes(bool $displayNotPublishedNodes): self
    {
        $this->displayNotPublishedNodes = $displayNotPublishedNodes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayingAllNodesStatuses(): bool
    {
        return $this->displayAllNodesStatuses;
    }

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses): self
    {
        $this->displayAllNodesStatuses = $displayAllNodesStatuses;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPage(int $page): self
    {
        $this->currentPage = $page > 0 ? $page : 1;

        return $this;
    }

    /**
     * @return int
     */
    protected function getPage(): int
    {
        return $this->currentPage;
    }

    public function enablePagination(): self
    {
        $this->pagination = true;
        return $this;
    }

    public function disablePagination(): self
    {
        $this->setPage(1);
        $this->pagination = false;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAssignation(): array
    {
        $assign = [
            'currentPage' => $this->getPage(),
            'pageCount' => $this->getPageCount(),
            'itemPerPage' => $this->getItemPerPage(),
            'itemCount' => $this->getItemCount(),
            'search' => $this->searchPattern,
            'nextPageQuery' => null,
            'previousPageQuery' => null,
        ];

        if ($this->getPageCount() > 1) {
            $assign['firstPageQuery'] = http_build_query(array_merge(
                $this->getQueryString(),
                ['page' => 1]
            ));
            $assign['lastPageQuery'] = http_build_query(array_merge(
                $this->getQueryString(),
                ['page' => $this->getPageCount()]
            ));
        }

        // compute next and prev page URL
        if ($this->currentPage > 1) {
            $previousQueryString = array_merge(
                $this->getQueryString(),
                ['page' => $this->getPage() - 1]
            );
            $assign['previousPageQuery'] = http_build_query($previousQueryString);
            $assign['previousQueryArray'] = $previousQueryString;
            $assign['previousPage'] = $this->getPage() - 1;
        }
        // compute next and prev page URL
        if ($this->getPage() < $this->getPageCount()) {
            $nextQueryString = array_merge(
                $this->getQueryString(),
                ['page' => $this->getPage() + 1]
            );
            $assign['nextPageQuery'] = http_build_query($nextQueryString);
            $assign['nextQueryArray'] = $nextQueryString;
            $assign['nextPage'] = $this->getPage() + 1;
        }

        return $assign;
    }

    protected function getQueryString(): array
    {
        return $this->queryArray ?? [];
    }

    /**
     * @return int
     */
    protected function getItemPerPage(): int
    {
        return $this->itemPerPage;
    }

    /**
     * Configure a custom item count per page.
     */
    public function setItemPerPage(int $itemPerPage): self
    {
        $this->itemPerPage = $itemPerPage > 0 ? $itemPerPage : 1;

        return $this;
    }

    /**
     * @return int
     */
    public function getPageCount(): int
    {
        return (int) ceil($this->getItemCount() / $this->getItemPerPage());
    }

    protected function handleRequestQuery(bool $disabled): void
    {
        if ($disabled || null === $this->request) {
            /*
             * Disable pagination and paginator
             */
            $this->disablePagination();
            return;
        }

        $field = $this->request->query->get('field');
        $ordering = $this->request->query->get('ordering');
        $search = $this->request->query->get('search');
        $itemPerPage = $this->request->query->get('item_per_page') ??
            $this->request->query->get('itemPerPage') ??
            $this->request->query->get('itemsPerPage');
        $page = $this->request->query->get('page');

        if (
            $this->allowRequestSorting &&
            \is_string($field) &&
            $field !== "" &&
            \is_string($ordering) &&
            \in_array(strtolower($ordering), ['asc', 'desc'])
        ) {
            $this->handleOrderingParam($field, $ordering);
            $this->queryArray['field'] = $field;
            $this->queryArray['ordering'] = $ordering;
        }

        if (
            $this->allowRequestSearching &&
            \is_string($search) &&
            $search !== ""
        ) {
            $this->handleSearchParam($search);
            $this->queryArray['search'] = $search;
        }

        if (
            \is_numeric($itemPerPage) &&
            ((int) $itemPerPage) > 0
        ) {
            $this->setItemPerPage((int) $itemPerPage);
        }

        if (
            \is_numeric($page) &&
            ((int) $page) > 1
        ) {
            $this->setPage((int) $page);
        } else {
            $this->setPage(1);
        }
    }

    protected function handleSearchParam(string $search): void
    {
        $this->searchPattern = $search;
    }

    protected function handleOrderingParam(string $field, string $ordering): void
    {
        // Do nothing on abstract
    }

    protected function validateOrderingFieldName(string $field): void
    {
        // check if field is a valid name without any SQL injection
        if (\preg_match('/^[a-zA-Z0-9_.]+$/', $field) !== 1) {
            throw new \InvalidArgumentException('Field name is not valid.');
        }
    }
}
