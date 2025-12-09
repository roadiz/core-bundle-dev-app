<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\NodeTreeDto;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final class NodeTreeDtoListManager extends EntityListManager
{
    #[\Override]
    protected function createPaginator(): void
    {
        $this->paginator = new NodeTreeDtoPaginator(
            $this->entityManager,
            Node::class,
            $this->getItemPerPage(),
            $this->filteringArray
        );
        $this->paginator->setTranslation($this->translation);
        $this->paginator->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
        $this->paginator->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
    }

    /**
     * @return array<NodeTreeDto>
     */
    #[\Override]
    public function getEntities(): array
    {
        if (true === $this->pagination && null !== $this->paginator) {
            $this->paginator->setItemsPerPage($this->getItemPerPage());

            // @phpstan-ignore-next-line
            return $this->paginator->findByAtPage($this->orderingArray, $this->currentPage);
        } else {
            $repository = $this->entityManager->getRepository(Node::class);
            if ($repository instanceof StatusAwareRepository) {
                $repository->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
                $repository->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());
            }

            return $repository->findByAsNodeTreeDto(
                $this->filteringArray,
                $this->orderingArray,
                $this->itemPerPage
            );
        }
    }
}
