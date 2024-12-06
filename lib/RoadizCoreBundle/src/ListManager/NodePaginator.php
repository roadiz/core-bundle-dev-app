<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\ListManager;

use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/**
 * A paginator class to filter node entities with limit and search.
 *
 * This class add some translation and security filters
 *
 * @extends Paginator<Node>
 */
#[Exclude]
class NodePaginator extends Paginator
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

    public function findByAtPage(array $order = [], int $page = 1): array
    {
        if (null !== $this->searchPattern) {
            return $this->searchByAtPage($order, $page);
        } else {
            /** @var NodeRepository $repository */
            $repository = $this->getRepository();
            if ($repository instanceof NodeRepository) {
                return $repository->findBy(
                    $this->criteria,
                    $order,
                    $this->getItemsPerPage(),
                    $this->getItemsPerPage() * ($page - 1),
                    $this->getTranslation()
                );
            }

            return $repository->findBy(
                $this->criteria,
                $order,
                $this->getItemsPerPage(),
                $this->getItemsPerPage() * ($page - 1)
            );
        }
    }

    public function getTotalCount(): int
    {
        if (null === $this->totalCount) {
            if (null !== $this->searchPattern) {
                $this->totalCount = $this->getRepository()
                    ->countSearchBy($this->searchPattern, $this->criteria);
            } else {
                $repository = $this->getRepository();
                if ($repository instanceof NodeRepository) {
                    $this->totalCount = $repository->countBy(
                        $this->criteria,
                        $this->getTranslation()
                    );
                }
                $this->totalCount = $repository->countBy(
                    $this->criteria
                );
            }
        }

        return $this->totalCount;
    }
}
