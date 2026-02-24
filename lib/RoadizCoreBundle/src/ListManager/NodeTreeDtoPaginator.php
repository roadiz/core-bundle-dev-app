<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\NodeTreeDto;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * A paginator class to filter node entities with limit and search.
 *
 * This class add some translation and security filters
 */
#[Exclude]
class NodeTreeDtoPaginator extends Paginator
{
    protected ?TranslationInterface $translation = null;

    public function getTranslation(): ?TranslationInterface
    {
        return $this->translation;
    }

    public function setTranslation(?TranslationInterface $translation = null): self
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * @return array<NodeTreeDto>
     */
    public function findByAtPage(array $order = [], int $page = 1): array
    {
        $repository = $this->getRepository();
        if (null !== $this->searchPattern) {
            return $repository->searchByAsNodeTreeDto(
                $this->searchPattern,
                $this->criteria,
                $order,
                $this->getItemsPerPage(),
                $this->getItemsPerPage() * ($page - 1)
            );
        }

        return $repository->findByAsNodeTreeDto(
            $this->criteria,
            $order,
            $this->getItemsPerPage(),
            $this->getItemsPerPage() * ($page - 1),
            $this->getTranslation()
        );
    }

    public function getTotalCount(): int
    {
        if (null === $this->totalCount) {
            if (null !== $this->searchPattern) {
                $this->totalCount = $this->getRepository()
                    ->countSearchBy($this->searchPattern, $this->criteria);
            } else {
                $this->totalCount = $this->getRepository()->countBy(
                    $this->criteria,
                    $this->getTranslation()
                );
            }
        }

        return $this->totalCount;
    }

    // @phpstan-ignore-next-line
    protected function getRepository(): NodeRepository
    {
        $repository = $this->em->getRepository(Node::class);
        $repository->setDisplayingNotPublishedNodes($this->isDisplayingNotPublishedNodes());
        $repository->setDisplayingAllNodesStatuses($this->isDisplayingAllNodesStatuses());

        return $repository;
    }
}
