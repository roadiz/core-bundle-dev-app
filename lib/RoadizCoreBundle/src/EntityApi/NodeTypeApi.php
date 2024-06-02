<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodeTypeRepository;

/**
 * @deprecated Use NodeTypeRepository directly
 */
class NodeTypeApi extends AbstractApi
{
    /**
     * @return NodeTypeRepository
     */
    public function getRepository()
    {
        return $this->managerRegistry->getRepository(NodeType::class);
    }
    /**
     * {@inheritdoc}
     */
    public function getBy(array $criteria, array $order = null)
    {
        return $this->getRepository()->findBy($criteria, $order);
    }
    /**
     * {@inheritdoc}
     */
    public function getOneBy(array $criteria, array $order = null)
    {
        return $this->getRepository()->findOneBy($criteria, $order);
    }
    /**
     * {@inheritdoc}
     */
    public function countBy(array $criteria)
    {
        return $this->getRepository()->countBy($criteria);
    }
}
