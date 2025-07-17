<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use RZ\Roadiz\CoreBundle\Repository\StatusAwareRepository;

/**
 * @deprecated Use NodeRepository directly
 */
class NodeApi extends AbstractApi
{
    #[\Override]
    public function getRepository(): NodeRepository
    {
        /** @var NodeRepository $repository */
        $repository = $this->managerRegistry->getRepository(Node::class);

        /*
         * We need to reset repository status state, because StatusAwareRepository is not a stateless service.
         * When using worker PHP runtimes (such as FrankenPHP or Swoole), this can lead to unpublish nodes being returned.
         */
        if ($repository instanceof StatusAwareRepository) {
            $repository->resetStatuses();
        }

        return $repository;
    }

    /**
     * @return array<Node>|Paginator<Node>
     */
    #[\Override]
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

    #[\Override]
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

    #[\Override]
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
