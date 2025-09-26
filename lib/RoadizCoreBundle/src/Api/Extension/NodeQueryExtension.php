<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final readonly class NodeQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private PreviewResolverInterface $previewResolver,
    ) {
    }

    #[\Override]
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass);
    }

    private function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
    ): void {
        if (Node::class !== $resourceClass) {
            return;
        }

        // Always exclude shadow nodes
        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq('o.shadow', ':shadow'))
            ->setParameter(':shadow', false);

        if ($this->previewResolver->isPreview()) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('o.status', ':status'))
                ->setParameter(':status', NodeStatus::PUBLISHED);

            return;
        }

        $alias = QueryBuilderHelper::addJoinOnce(
            $queryBuilder,
            $queryNameGenerator,
            'o',
            'nodeSources',
            Join::INNER_JOIN
        );
        $queryBuilder
            ->andWhere($queryBuilder->expr()->lte($alias.'.publishedAt', ':lte_published_at'))
            ->andWhere($queryBuilder->expr()->eq('o.status', ':status'))
            ->setParameter(':lte_published_at', new \DateTime())
            ->setParameter(':status', NodeStatus::PUBLISHED);

        return;
    }

    #[\Override]
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass);
    }
}
