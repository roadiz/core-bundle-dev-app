<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderNodesSourcesBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Filter on nodeType fields when criteria contains nodeType. prefix.
 */
class NodesSourcesNodeFilter implements EventSubscriberInterface
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderNodesSourcesBuildEvent::class => [['onNodesSourcesQueryBuilderBuild', -10]],
        ];
    }

    protected function supports(QueryBuilderNodesSourcesBuildEvent $event): bool
    {
        return $event->supports() && str_contains($event->getProperty(), 'node.');
    }

    public function onNodesSourcesQueryBuilderBuild(QueryBuilderNodesSourcesBuildEvent $event): void
    {
        if ($this->supports($event)) {
            // Prevent other query builder filters to execute
            $event->stopPropagation();
            $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
            $qb = $event->getQueryBuilder();
            $baseKey = $simpleQB->getParameterKey($event->getProperty());

            if (
                !$simpleQB->joinExists(
                    $simpleQB->getRootAlias(),
                    EntityRepository::NODE_ALIAS
                )
            ) {
                $qb->innerJoin(
                    $simpleQB->getRootAlias().'.node',
                    EntityRepository::NODE_ALIAS
                );
            }

            $prefix = EntityRepository::NODE_ALIAS.'.';
            $key = str_replace('node.', '', $event->getProperty());
            $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
        }
    }
}
