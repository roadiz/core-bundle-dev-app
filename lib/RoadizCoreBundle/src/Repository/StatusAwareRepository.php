<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @template TEntityClass of object
 *
 * @extends EntityRepository<TEntityClass>
 *
 * @deprecated stateful repositories are deprecated and should not be used as services
 */
abstract class StatusAwareRepository extends EntityRepository implements StatusAwareRepositoryInterface
{
    private bool $displayNotPublishedNodes;
    private bool $displayAllNodesStatuses;

    /**
     * @param class-string<TEntityClass> $entityClass
     */
    public function __construct(
        ManagerRegistry $registry,
        string $entityClass,
        protected readonly PreviewResolverInterface $previewResolver,
        EventDispatcherInterface $dispatcher,
        protected readonly Security $security,
    ) {
        parent::__construct($registry, $entityClass, $dispatcher);

        $this->resetStatuses();
    }

    /**
     * @deprecated do not use repository stateful methods in services
     */
    public function resetStatuses(): self
    {
        $this->displayNotPublishedNodes = false;
        $this->displayAllNodesStatuses = false;

        return $this;
    }

    /**
     * @deprecated do not use repository stateful methods in services
     */
    #[\Override]
    public function isDisplayingNotPublishedNodes(): bool
    {
        return $this->displayNotPublishedNodes;
    }

    /**
     * @return $this
     *
     * @deprecated do not use repository stateful methods in services
     */
    public function setDisplayingNotPublishedNodes(bool $displayNotPublishedNodes): self
    {
        $this->displayNotPublishedNodes = $displayNotPublishedNodes;

        return $this;
    }

    /**
     * @deprecated do not use repository stateful methods in services
     */
    #[\Override]
    public function isDisplayingAllNodesStatuses(): bool
    {
        return $this->displayAllNodesStatuses;
    }

    /**
     * Switch repository to disable any security on Node status. To use ONLY in order to
     * view deleted and archived nodes.
     *
     * @return $this
     *
     * @deprecated do not use repository stateful methods in services
     */
    public function setDisplayingAllNodesStatuses(bool $displayAllNodesStatuses): self
    {
        $this->displayAllNodesStatuses = $displayAllNodesStatuses;

        return $this;
    }

    #[\Override]
    public function alterQueryBuilderWithAuthorizationChecker(
        QueryBuilder $qb,
        string $prefix = EntityRepository::NODE_ALIAS,
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
            $qb->andWhere($qb->expr()->lte($prefix.'.status', ':status'));
        } else {
            $qb->andWhere($qb->expr()->eq($prefix.'.status', ':status'));
        }
        $qb->setParameter('status', NodeStatus::PUBLISHED);

        return $qb;
    }
}
