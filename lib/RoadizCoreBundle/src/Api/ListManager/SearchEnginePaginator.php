<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\ListManager;

use ApiPlatform\State\Pagination\PaginatorInterface;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class SearchEnginePaginator implements PaginatorInterface, \IteratorAggregate
{
    private bool $handled = false;

    public function __construct(private readonly SearchEngineListManager $listManager)
    {
    }

    private function handleOnce(): void
    {
        if (false === $this->handled) {
            $this->listManager->handle();
            $this->handled = true;
        }
    }

    #[\Override]
    public function count(): int
    {
        $this->handleOnce();

        return max(0, $this->listManager->getItemCount());
    }

    #[\Override]
    public function getLastPage(): float
    {
        $this->handleOnce();
        $lastPage = $this->listManager->getPageCount();

        return max($lastPage, 1);
    }

    #[\Override]
    public function getTotalItems(): float
    {
        $this->handleOnce();

        return $this->listManager->getItemCount();
    }

    #[\Override]
    public function getCurrentPage(): float
    {
        $this->handleOnce();

        return $this->listManager->getAssignation()['currentPage'];
    }

    #[\Override]
    public function getItemsPerPage(): float
    {
        $this->handleOnce();

        return $this->listManager->getAssignation()['itemPerPage'];
    }

    #[\Override]
    public function getIterator(): \Traversable
    {
        $this->handleOnce();
        $entities = $this->listManager->getEntities();

        return new \ArrayIterator($entities);
    }
}
