<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;

/**
 * @deprecated Use NodeRepository directly
 */
class NodeApi extends AbstractApi
{
    public function getRepository(): NodeRepository
    {
        // phpstan cannot resolve repository type.
        /** @var NodeRepository $repository */
        $repository = $this->managerRegistry
                    ->getRepository(Node::class)
                    ->setDisplayingNotPublishedNodes(false)
                    ->setDisplayingAllNodesStatuses(false);

        return $repository;
    }

    /**
     * @return array<Node>|Paginator<Node>
     */
    public function getBy(
        array $criteria,
        ?array $order = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array|Paginator {
        if (!in_array('translation.available', $criteria, true)) {
            $criteria['translation.available'] = true;
        }

        return $this->getRepository()
                    ->findBy(
                        $criteria,
                        $order,
                        $limit,
                        $offset,
                        null
                    );
    }

    public function countBy(array $criteria): int
    {
        if (!in_array('translation.available', $criteria, true)) {
            $criteria['translation.available'] = true;
        }

        return $this->getRepository()
                    ->countBy(
                        $criteria,
                        null
                    );
    }

    public function getOneBy(array $criteria, ?array $order = null): ?Node
    {
        if (!in_array('translation.available', $criteria, true)) {
            $criteria['translation.available'] = true;
        }

        return $this->getRepository()
                    ->findOneBy(
                        $criteria,
                        $order,
                        null
                    );
    }
}
