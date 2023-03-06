<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\ListManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Doctrine\Common\Collections\ArrayCollection;

final class SolrPaginator implements PaginatorInterface, \IteratorAggregate
{
    private bool $handled = false;
    private SolrSearchListManager $listManager;

    /**
     * @param SolrSearchListManager $listManager
     */
    public function __construct(SolrSearchListManager $listManager)
    {
        $this->listManager = $listManager;
    }

    protected function handleOnce(): void
    {
        if (false === $this->handled) {
            $this->listManager->handle();
            $this->handled = true;
        }
    }

    public function count(): int
    {
        $this->handleOnce();
        return $this->listManager->getItemCount();
    }

    public function getLastPage(): float
    {
        $this->handleOnce();
        return $this->listManager->getPageCount() - 1;
    }

    public function getTotalItems(): float
    {
        $this->handleOnce();
        return $this->listManager->getItemCount();
    }

    public function getCurrentPage(): float
    {
        $this->handleOnce();
        return $this->listManager->getAssignation()['currentPage'];
    }

    public function getItemsPerPage(): float
    {
        $this->handleOnce();
        return $this->listManager->getAssignation()['itemPerPage'];
    }

    public function getIterator(): \Traversable
    {
        $this->handleOnce();
        return new ArrayCollection($this->listManager->getEntities());
    }
}
