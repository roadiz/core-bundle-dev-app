<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Filter on translation fields when criteria contains translation. prefix.
 *
 * @package RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter
 */
class NodeTranslationFilter implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderBuildEvent::class => [
                // This event must be the last to perform
                ['onTranslationPrefixFilter', 0],
                ['onTranslationFilter', -10],
            ]
        ];
    }

    /**
     * @param QueryBuilderBuildEvent $event
     *
     * @return bool
     */
    protected function supports(QueryBuilderBuildEvent $event): bool
    {
        return $event->supports() &&
            $event->getActualEntityName() === Node::class &&
            str_contains($event->getProperty(), 'translation');
    }

    /**
     * @param QueryBuilderBuildEvent $event
     */
    public function onTranslationPrefixFilter(QueryBuilderBuildEvent $event): void
    {
        if ($this->supports($event)) {
            $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
            if (str_contains($event->getProperty(), 'translation.')) {
                // Prevent other query builder filters to execute
                $event->stopPropagation();
                $qb = $event->getQueryBuilder();
                $baseKey = $simpleQB->getParameterKey($event->getProperty());

                if (
                    !$simpleQB->joinExists(
                        $simpleQB->getRootAlias(),
                        EntityRepository::NODESSOURCES_ALIAS
                    )
                ) {
                    $qb->innerJoin(
                        $simpleQB->getRootAlias() . '.nodeSources',
                        EntityRepository::NODESSOURCES_ALIAS
                    );
                }

                if (
                    !$simpleQB->joinExists(
                        $simpleQB->getRootAlias(),
                        EntityRepository::TRANSLATION_ALIAS
                    )
                ) {
                    $qb->innerJoin(
                        EntityRepository::NODESSOURCES_ALIAS . '.translation',
                        EntityRepository::TRANSLATION_ALIAS
                    );
                }

                $prefix = EntityRepository::TRANSLATION_ALIAS . '.';
                $key = str_replace('translation.', '', $event->getProperty());
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
            }
        }
    }

    /**
     * @param QueryBuilderBuildEvent $event
     */
    public function onTranslationFilter(QueryBuilderBuildEvent $event): void
    {
        if ($this->supports($event)) {
            $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
            if ($event->getProperty() === 'translation') {
                // Prevent other query builder filters to execute
                $event->stopPropagation();
                $qb = $event->getQueryBuilder();
                $baseKey = $simpleQB->getParameterKey($event->getProperty());

                if (
                    !$simpleQB->joinExists(
                        $simpleQB->getRootAlias(),
                        EntityRepository::NODESSOURCES_ALIAS
                    )
                ) {
                    $qb->innerJoin(
                        $simpleQB->getRootAlias() . '.nodeSources',
                        EntityRepository::NODESSOURCES_ALIAS
                    );
                }

                $prefix = EntityRepository::NODESSOURCES_ALIAS . '.';
                $key = $event->getProperty();
                $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
            }
        }
    }
}
