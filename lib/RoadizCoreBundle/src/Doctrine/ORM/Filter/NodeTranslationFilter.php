<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM\Filter;

use RZ\Roadiz\CoreBundle\Doctrine\Event\QueryBuilder\QueryBuilderBuildEvent;
use RZ\Roadiz\CoreBundle\Doctrine\ORM\SimpleQueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Repository\EntityRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Filter on translation fields when criteria contains translation. prefix.
 */
class NodeTranslationFilter implements EventSubscriberInterface
{
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            QueryBuilderBuildEvent::class => [
                // This event must be the last to perform
                ['onTranslationPrefixFilter', 0],
                ['onTranslationFilter', -10],
            ],
        ];
    }

    protected function supports(QueryBuilderBuildEvent $event): bool
    {
        return $event->supports()
            && Node::class === $event->getActualEntityName()
            && str_contains($event->getProperty(), 'translation');
    }

    public function onTranslationPrefixFilter(QueryBuilderBuildEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
        $rootAlias = $simpleQB->getRootAlias();

        if (null === $rootAlias || !str_contains($event->getProperty(), 'translation.')) {
            return;
        }
        // Prevent other query builder filters to execute
        $event->stopPropagation();
        $qb = $event->getQueryBuilder();
        $baseKey = $simpleQB->getParameterKey($event->getProperty());

        if (
            !$simpleQB->joinExists(
                $rootAlias,
                EntityRepository::NODESSOURCES_ALIAS
            )
        ) {
            $qb->innerJoin(
                $rootAlias.'.nodeSources',
                EntityRepository::NODESSOURCES_ALIAS
            );
        }

        if (
            !$simpleQB->joinExists(
                $rootAlias,
                EntityRepository::TRANSLATION_ALIAS
            )
        ) {
            $qb->innerJoin(
                EntityRepository::NODESSOURCES_ALIAS.'.translation',
                EntityRepository::TRANSLATION_ALIAS
            );
        }

        $prefix = EntityRepository::TRANSLATION_ALIAS.'.';
        $key = str_replace('translation.', '', $event->getProperty());
        $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
    }

    public function onTranslationFilter(QueryBuilderBuildEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $simpleQB = new SimpleQueryBuilder($event->getQueryBuilder());
        $rootAlias = $simpleQB->getRootAlias();

        if (null === $rootAlias || 'translation' !== $event->getProperty()) {
            return;
        }
        // Prevent other query builder filters to execute
        $event->stopPropagation();
        $qb = $event->getQueryBuilder();
        $baseKey = $simpleQB->getParameterKey($event->getProperty());

        if (
            !$simpleQB->joinExists(
                $rootAlias,
                EntityRepository::NODESSOURCES_ALIAS
            )
        ) {
            $qb->innerJoin(
                $rootAlias.'.nodeSources',
                EntityRepository::NODESSOURCES_ALIAS
            );
        }

        $prefix = EntityRepository::NODESSOURCES_ALIAS.'.';
        $key = $event->getProperty();
        $qb->andWhere($simpleQB->buildExpressionWithoutBinding($event->getValue(), $prefix, $key, $baseKey));
    }
}
