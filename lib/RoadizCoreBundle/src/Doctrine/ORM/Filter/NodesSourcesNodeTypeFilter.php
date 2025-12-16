<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Doctrine\Event\FilterNodesSourcesQueryBuilderCriteriaEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesApplyEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class NodesSourcesNodeTypeFilter implements EventSubscriberInterface
{
    public function __construct(
        private NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', -9]],
            QueryBuilderNodesSourcesApplyEvent::class => [['onNodesSourcesQueryBuilderApply', -9]],
        ];
    }

    protected function supports(FilterNodesSourcesQueryBuilderCriteriaEvent $event): bool
    {
        return $event->supports()
            && 'node.nodeType' === $event->getProperty()
            && (
                $event->getValue() instanceof NodeType
                || (is_array($event->getValue())
                    && count($event->getValue()) > 0
                    && $event->getValue()[0] instanceof NodeType)
            );
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
        $value = $event->getValue();

        if (null === $rootAlias) {
            return;
        }

        if ($value instanceof NodeType) {
            $qb->andWhere($qb->expr()->isInstanceOf(
                $rootAlias,
                $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($value)
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
                        $rootAlias,
                        $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType)
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
}
