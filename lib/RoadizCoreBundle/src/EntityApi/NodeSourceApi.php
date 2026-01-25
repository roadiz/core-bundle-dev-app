<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\EntityApi;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;

/**
 * @deprecated Use NodesSourcesRepository directly
 */
class NodeSourceApi extends AbstractApi
{
    public function __construct(
        ManagerRegistry $managerRegistry,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
        parent::__construct($managerRegistry);
    }

    /**
     * @var class-string<NodesSources>
     */
    protected string $nodeSourceClassName = NodesSources::class;

    /**
     * @return class-string<NodesSources>
     */
    protected function getNodeSourceClassName(?array $criteria = null): string
    {
        if (isset($criteria['node.nodeType']) && $criteria['node.nodeType'] instanceof NodeType) {
            /** @var class-string<NodesSources> $entityClassName */
            $entityClassName = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($criteria['node.nodeType']);
            $this->nodeSourceClassName = $entityClassName;
            unset($criteria['node.nodeType']);
        } elseif (
            isset($criteria['node.nodeType'])
            && is_array($criteria['node.nodeType'])
            && 1 === count($criteria['node.nodeType'])
            && $criteria['node.nodeType'][0] instanceof NodeType
        ) {
            /** @var class-string<NodesSources> $entityClassName */
            $entityClassName = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($criteria['node.nodeType'][0]);
            $this->nodeSourceClassName = $entityClassName;
            unset($criteria['node.nodeType']);
        } else {
            $this->nodeSourceClassName = NodesSources::class;
        }

        return $this->nodeSourceClassName;
    }

    #[\Override]
    public function getRepository(): NodesSourcesRepository
    {
        // @phpstan-ignore-next-line
        return $this->managerRegistry->getRepository($this->nodeSourceClassName);
    }

    /**
     * @return array<NodesSources>|Paginator<NodesSources>
     */
    #[\Override]
    public function getBy(
        array $criteria,
        ?array $order = null,
        ?int $limit = null,
        ?int $offset = null,
    ): array|Paginator {
        $this->getNodeSourceClassName($criteria);

        return $this->getRepository()
                    ->findBy(
                        $criteria,
                        $order,
                        $limit,
                        $offset
                    );
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws NonUniqueResultException
     */
    #[\Override]
    public function countBy(array $criteria): int
    {
        $this->getNodeSourceClassName($criteria);

        return $this->getRepository()
                    ->countBy(
                        $criteria
                    );
    }

    /**
     * @throws NonUniqueResultException
     */
    #[\Override]
    public function getOneBy(array $criteria, ?array $order = null): ?NodesSources
    {
        $this->getNodeSourceClassName($criteria);

        return $this->getRepository()
                    ->findOneBy(
                        $criteria,
                        $order
                    );
    }
}
