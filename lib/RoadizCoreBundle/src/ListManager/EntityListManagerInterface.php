<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;

interface EntityListManagerInterface
{
    public const ITEM_PER_PAGE = 20;

    /**
     * @param bool $allowRequestSorting
     * @return $this
     */
    public function setAllowRequestSorting(bool $allowRequestSorting): self;

    /**
     * @param bool $allowRequestSearching
     * @return $this
     */
    public function setAllowRequestSearching(bool $allowRequestSearching): self;

    /**
     * @return bool
     */
    public function isDisplayingNotPublishedNodes(): bool;

    /**
     * @param bool $displayNotPublishedNodes
     * @return EntityListManagerInterface
     */
    public function setDisplayingNotPublishedNodes(bool $displayNotPublishedNodes): self;

    /**
     * @return bool
     */
    public function isDisplayingAllNodesStatuses(): bool;

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     *
     * @param bool $displayAllNodesStatuses
     * @return EntityListManagerInterface
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses): self;

    /**
     * Handle request to find filter to apply to entity listing.
     *
     * @param bool $disabled Disable pagination and filtering over GET params
     * @return void
     */
    public function handle(bool $disabled = false): void;

    /**
     * Configure a custom current page.
     *
     * @param int $page
     *
     * @return EntityListManagerInterface
     */
    public function setPage(int $page): self;

    /**
     * @return EntityListManagerInterface
     */
    public function disablePagination(): self;

    /**
     * Get Twig assignation to render list details.
     *
     * ** Fields:
     *
     * * description [string]
     * * search [string]
     * * currentPage [int]
     * * pageCount [int]
     * * itemPerPage [int]
     * * itemCount [int]
     * * previousPage [int]
     * * nextPage [int]
     * * nextPageQuery [string]
     * * previousPageQuery [string]
     * * previousQueryArray [array]
     * * nextQueryArray [array]
     *
     * @return array
     */
    public function getAssignation(): array;

    /**
     * @return int
     */
    public function getItemCount(): int;

    /**
     * @return int
     */
    public function getPageCount(): int;

    /**
     * Return filtered entities.
     *
     * @return array|DoctrinePaginator
     */
    public function getEntities(): array|DoctrinePaginator;

    /**
     * Configure a custom item count per page.
     *
     * @param int $itemPerPage
     *
     * @return EntityListManagerInterface
     */
    public function setItemPerPage(int $itemPerPage): self;
}
