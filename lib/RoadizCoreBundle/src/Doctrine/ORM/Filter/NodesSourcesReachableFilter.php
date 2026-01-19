<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Doctrine\Event\FilterNodesSourcesQueryBuilderCriteriaEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryNodesSourcesEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NodesSourcesReachableFilter implements EventSubscriberInterface
{
    public const array PARAMETER = [
        'node.nodeType.reachable',
        'reachable',
    ];

    public function __construct(
        private NodeTypes $nodeTypesBag,
        private readonly NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', 41]],
            QueryBuilderNodesSourcesApplyEvent::class => [['onNodesSourcesQueryBuilderApply', 41]],
            QueryNodesSourcesEvent::class => [['onQueryNodesSourcesEvent', 0]],
        ];
    }

    protected function supports(FilterNodesSourcesQueryBuilderCriteriaEvent $event): bool
    {
        return $event->supports()
            && in_array($event->getProperty(), self::PARAMETER)
            && is_bool($event->getValue());
    }

    public function onNodesSourcesQueryBuilderBuild(QueryBuilderNodesSourcesBuildEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        // Prevent other query builder filters to execute
        $event->stopPropagation();
        $qb = $event->getQueryBuilder();
        $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
        $rootAlias = $simpleQB->getRootAlias();
        $value = (bool) $event->getValue();

        $nodeTypes = array_unique(array_filter($this->nodeTypesBag->all(), fn (NodeType $nodeType) => $nodeType->getReachable() === $value));

        if (null !== $rootAlias && count($nodeTypes) > 0) {
            $orX = $qb->expr()->orX();
            /** @var NodeType $nodeType */
            foreach ($nodeTypes as $nodeType) {
                $orX->add($qb->expr()->isInstanceOf(
                    $rootAlias,
                    $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType)
                ));
            }
            $qb->andWhere($orX);
        }
    }

    public function onNodesSourcesQueryBuilderApply(QueryBuilderNodesSourcesApplyEvent $event): void
    {
        if ($this->supports($event)) {
            // Prevent other query builder filters to execute
            $event->stopPropagation();
        }
    }

    public function onQueryNodesSourcesEvent(QueryNodesSourcesEvent $event): void
    {
        // TODO: Find a way to reduce NodeSource query joins when filtered by node-types.
    }
}
