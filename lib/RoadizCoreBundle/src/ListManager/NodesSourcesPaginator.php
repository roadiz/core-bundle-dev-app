<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
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
    #[\Override]
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

    #[\Override]
    public function findByAtPage(array $order = [], int $page = 1): array
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

    #[\Override]
    protected function getRepository(): NodesSourcesRepository
    {
        /** @var NodesSourcesRepository $repository */
        $repository = $this->em->getRepository($this->entityName);
        $repository->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
        $repository->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());

        return $repository;
    }
}
