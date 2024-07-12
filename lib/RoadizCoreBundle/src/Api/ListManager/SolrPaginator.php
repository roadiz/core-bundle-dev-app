<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\ListManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class SolrPaginator implements PaginatorInterface, \IteratorAggregate
{
    private bool $handled = false;

    public function __construct(private readonly SolrSearchListManager $listManager)
    {
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
        $lastPage = $this->listManager->getPageCount();
        return max($lastPage, 1);
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
        $entities = $this->listManager->getEntities();
        return new \ArrayIterator($entities);
    }
}
