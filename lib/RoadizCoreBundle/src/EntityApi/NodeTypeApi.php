<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\Tools\Pagination\Paginator;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepository;

/**
 * @deprecated Use NodeTypeRepository directly
 */
class NodeTypeApi extends AbstractApi
{
    public function getRepository(): NodeTypeRepository
    {
        return $this->managerRegistry->getRepository(NodeType::class);
    }

    public function getBy(array $criteria, ?array $order = null): array|Paginator
    {
        return $this->getRepository()->findBy($criteria, $order);
    }

    public function getOneBy(array $criteria, ?array $order = null): ?NodeType
    {
        return $this->getRepository()->findOneBy($criteria, $order);
    }

    public function countBy(array $criteria): int
    {
        return $this->getRepository()->countBy($criteria);
    }
}
