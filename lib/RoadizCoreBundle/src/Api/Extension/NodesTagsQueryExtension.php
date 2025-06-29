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
use RZ\Roadiz\CoreBundle\Entity\Tag;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final readonly class NodesTagsQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    use NodesSourcesStatusExtensionTrait;

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

    private function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
    ): void {
        if (
            Tag::class !== $resourceClass
        ) {
            return;
        }

        $parts = $queryBuilder->getDQLPart('join');
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if (!\is_array($parts) || !isset($parts[$rootAlias])) {
            return;
        }

        $existingJoin = QueryBuilderHelper::getExistingJoin($queryBuilder, 'o', 'nodesTags');
        if (null === $existingJoin || !$existingJoin->getAlias()) {
            return;
        }
        $existingNodeJoin = QueryBuilderHelper::getExistingJoin(
            $queryBuilder,
            $existingJoin->getAlias(),
            'node'
        );
        if (null === $existingNodeJoin || !$existingNodeJoin->getAlias()) {
            return;
        }

        $alias = QueryBuilderHelper::addJoinOnce(
            $queryBuilder,
            $queryNameGenerator,
            $existingJoin->getAlias(),
            'nodeSources',
            Join::INNER_JOIN
        );

        $this->alterQueryBuilderWithStatus($queryBuilder, $alias);
    }
}
