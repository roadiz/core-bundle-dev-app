<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Doctrine\Event\FilterNodesSourcesQueryBuilderCriteriaEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryNodesSourcesEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter
 */
final class NodesSourcesReachableFilter implements EventSubscriberInterface
{
    public const PARAMETER = [
        'node.nodeType.reachable',
        'reachable'
    ];

    public function __construct(private readonly NodeTypes $nodeTypesBag)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', 41]],
            QueryBuilderNodesSourcesApplyEvent::class => [['onNodesSourcesQueryBuilderApply', 41]],
            QueryNodesSourcesEvent::class => [['onQueryNodesSourcesEvent', 0]],
        ];
    }

    /**
     * @param FilterNodesSourcesQueryBuilderCriteriaEvent $event
     *
     * @return bool
     */
    protected function supports(FilterNodesSourcesQueryBuilderCriteriaEvent $event): bool
    {
        return $event->supports() &&
            in_array($event->getProperty(), self::PARAMETER) &&
            is_bool($event->getValue());
    }

    /**
     * @param QueryBuilderNodesSourcesBuildEvent $event
     */
    public function onNodesSourcesQueryBuilderBuild(QueryBuilderNodesSourcesBuildEvent $event): void
    {
        if ($this->supports($event)) {
            // Prevent other query builder filters to execute
            $event->stopPropagation();
            $qb = $event->getQueryBuilder();
            $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
            $value = (bool) $event->getValue();

            $nodeTypes = array_unique(array_filter($this->nodeTypesBag->all(), function (NodeType $nodeType) use ($value) {
                return $nodeType->getReachable() === $value;
            }));

            if (count($nodeTypes) > 0) {
                $orX = $qb->expr()->orX();
                /** @var NodeType $nodeType */
                foreach ($nodeTypes as $nodeType) {
                    $orX->add($qb->expr()->isInstanceOf(
                        $simpleQB->getRootAlias(),
                        $nodeType->getSourceEntityFullQualifiedClassName()
                    ));
                }
                $qb->andWhere($orX);
            }
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
