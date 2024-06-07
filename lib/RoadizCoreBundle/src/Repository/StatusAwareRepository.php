<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template TEntityClass of object
 * @extends EntityRepository<TEntityClass>
 */
abstract class StatusAwareRepository extends EntityRepository
{
    private bool $displayNotPublishedNodes;
    private bool $displayAllNodesStatuses;

    /**
     * @param ManagerRegistry $registry
     * @param class-string<TEntityClass> $entityClass
     * @param PreviewResolverInterface $previewResolver
     * @param EventDispatcherInterface $dispatcher
     * @param Security $security
     */
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        protected readonly PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        protected readonly Security $security
    ) {
        parent::__construct($registry, $entityClass, $dispatcher);

        $this->displayNotPublishedNodes = false;
        $this->displayAllNodesStatuses = false;
    }


    /**
     * @return bool
     */
    public function isDisplayingNotPublishedNodes(): bool
    {
        return $this->displayNotPublishedNodes;
    }

    /**
     * @param bool $displayNotPublishedNodes
     * @return $this
     */
    public function setDisplayingNotPublishedNodes(bool $displayNotPublishedNodes): self
    {
        $this->displayNotPublishedNodes = $displayNotPublishedNodes;
        return $this;
    }

    /**
     * @return bool
     */
    public function isDisplayingAllNodesStatuses(): bool
    {
        return $this->displayAllNodesStatuses;
    }

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     *
     * @param bool $displayAllNodesStatuses
     * @return $this
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses): self
    {
        $this->displayAllNodesStatuses = $displayAllNodesStatuses;
        return $this;
    }

    /**
     * @param QueryBuilder $qb
     * @param string $prefix
     * @return QueryBuilder
     */
    public function alterQueryBuilderWithAuthorizationChecker(
        QueryBuilder $qb,
        string $prefix = EntityRepository::NODE_ALIAS
    ): QueryBuilder {
        if (true === $this->isDisplayingAllNodesStatuses()) {
            // do not filter on status
            return $qb;
        }
        /*
         * Check if user can see not-published node based on its Token
         * and context.
         */
        if (true === $this->isDisplayingNotPublishedNodes() || $this->previewResolver->isPreview()) {
            $qb->andWhere($qb->expr()->lte($prefix . '.status', Node::PUBLISHED));
        } else {
            $qb->andWhere($qb->expr()->eq($prefix . '.status', Node::PUBLISHED));
        }

        return $qb;
    }
}
