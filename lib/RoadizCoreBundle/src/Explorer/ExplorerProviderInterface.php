<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Explorer;

interface ExplorerProviderInterface
{
    public function toExplorerItem(mixed $item): ?ExplorerItemInterface;

    /**
     * @param array $options Options (search, page, itemPerPage…)
     *
     * @return ExplorerItemInterface[]
     */
    public function getItems(array $options = []): array;

    /**
     * @param array $options Options (search, page, itemPerPage…)
     */
    public function getFilters(array $options = []): array;

    /**
     * @param array<string|int> $ids
     *
     * @return ExplorerItemInterface[]
     */
    public function getItemsById(array $ids = []): array;

    /**
     * Check if object can be handled be current ExplorerProvider.
     */
    public function supports(mixed $item): bool;
}
