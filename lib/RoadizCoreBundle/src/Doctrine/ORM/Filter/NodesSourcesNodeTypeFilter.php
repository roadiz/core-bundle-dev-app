<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Doctrine\Event\FilterNodesSourcesQueryBuilderCriteriaEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter
 */
final class NodesSourcesNodeTypeFilter implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', -9]],
            QueryBuilderNodesSourcesApplyEvent::class => [['onNodesSourcesQueryBuilderApply', -9]],
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
            $event->getProperty() === 'node.nodeType' &&
            (
                $event->getValue() instanceof NodeType ||
                (is_array($event->getValue()) &&
                    count($event->getValue()) > 0 &&
                    $event->getValue()[0] instanceof NodeType)
            );
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
            $value = $event->getValue();

            if ($value instanceof NodeType) {
                $qb->andWhere($qb->expr()->isInstanceOf(
                    $simpleQB->getRootAlias(),
                    $value->getSourceEntityFullQualifiedClassName()
                ));
            } elseif (is_array($value)) {
                $nodeTypes = [];
                foreach ($value as $nodeType) {
                    if ($nodeType instanceof NodeType) {
                        $nodeTypes[] = $nodeType;
                    }
                }
                $nodeTypes = array_unique($nodeTypes);

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
    }

    public function onNodesSourcesQueryBuilderApply(QueryBuilderNodesSourcesApplyEvent $event): void
    {
        if ($this->supports($event)) {
            // Prevent other query builder filters to execute
            $event->stopPropagation();
        }
    }
}
