<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * A paginator class to filter node-sources entities with limit and search.
 *
 * This class add authorizationChecker filters.
 *
 * @extends Paginator<NodesSources>
 */
#[Exclude]
class NodesSourcesPaginator extends Paginator
{
    /**
     * {@inheritdoc}
     */
    public function getTotalCount(): int
    {
        if (null === $this->totalCount) {
            if (null !== $this->searchPattern) {
                $this->totalCount = $this->getRepository()->countSearchBy($this->searchPattern, $this->criteria);
            } else {
                $this->totalCount = $this->getRepository()->countBy($this->criteria);
            }
        }

        return $this->totalCount;
    }

    public function findByAtPage(array $order = [], int $page = 1): array|DoctrinePaginator
    {
        if (null !== $this->searchPattern) {
            return $this->searchByAtPage($order, $page);
        } else {
            return $this->getRepository()->findBy(
                $this->criteria,
                $order,
                $this->getItemsPerPage(),
                $this->getItemsPerPage() * ($page - 1)
            );
        }
    }
}
