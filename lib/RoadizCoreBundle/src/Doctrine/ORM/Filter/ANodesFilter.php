<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @package RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter
 */
class ANodesFilter implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', 40]],
            QueryBuilderBuildEvent::class => [['onNodeQueryBuilderBuild', 30]]
        ];
    }

    /**
     * @return string
     */
    protected function getProperty(): string
    {
        return 'aNodes';
    }

    /**
     * @return string
     */
    protected function getNodeJoinAlias(): string
    {
        return 'a_n';
    }

    /**
     * @param QueryBuilderBuildEvent $event
     */
    public function onNodeQueryBuilderBuild(QueryBuilderBuildEvent $event): void
    {
        if ($event->supports() && $event->getActualEntityName() === Node::class) {
            $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
            if (str_contains($event->getProperty(), $this->getProperty() . '.')) {
                // Prevent other query builder filters to execute
                $event->stopPropagation();
                $qb = $event->getQueryBuilder();
                $baseKey = $simpleQB->getParameterKey($event->getProperty());

                if (
                    !$simpleQB->joinExists(
                        $simpleQB->getRootAlias(),
                        $this->getNodeJoinAlias()
                    )
                ) {
                    $qb->innerJoin(
                        $simpleQB->getRootAlias() . '.' . $this->getProperty(),
                        $this->getNodeJoinAlias()
                    );
                }

                $prefix = $this->getNodeJoinAlias() . '.';
                $key = str_replace($this->getProperty() . '.', '', $event->getProperty());

                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
            }
        }
    }

    /**
     * @param QueryBuilderNodesSourcesBuildEvent $event
     */
    public function onNodesSourcesQueryBuilderBuild(QueryBuilderNodesSourcesBuildEvent $event): void
    {
        if ($event->supports()) {
            $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
            if (str_contains($event->getProperty(), 'node.' . $this->getProperty() . '.')) {
                // Prevent other query builder filters to execute
                $event->stopPropagation();
                $qb = $event->getQueryBuilder();
                $baseKey = $simpleQB->getParameterKey($event->getProperty());

                if (
                    !$simpleQB->joinExists(
                        $simpleQB->getRootAlias(),
                        EntityRepository::NODE_ALIAS
                    )
                ) {
                    $qb->innerJoin(
                        $simpleQB->getRootAlias() . '.node',
                        EntityRepository::NODE_ALIAS
                    );
                }

                if (
                    !$simpleQB->joinExists(
                        $simpleQB->getRootAlias(),
                        $this->getNodeJoinAlias()
                    )
                ) {
                    $qb->innerJoin(
                        EntityRepository::NODE_ALIAS . '.' . $this->getProperty(),
                        $this->getNodeJoinAlias()
                    );
                }

                $prefix = $this->getNodeJoinAlias() . '.';
                $key = str_replace('node.' . $this->getProperty() . '.', '', $event->getProperty());

                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
            }
        }
    }
}
