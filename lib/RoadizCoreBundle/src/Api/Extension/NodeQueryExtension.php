<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final class NodeQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    private PreviewResolverInterface $previewResolver;

    public function __construct(
        PreviewResolverInterface $previewResolver
    ) {
        $this->previewResolver = $previewResolver;
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        string $operationName = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }

    private function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if ($resourceClass !== Node::class) {
            return;
        }

        if ($this->previewResolver->isPreview()) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte('o.status', ':status'))
                ->setParameter(':status', Node::PUBLISHED);
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
            ->andWhere($queryBuilder->expr()->lte($alias . '.publishedAt', ':lte_published_at'))
            ->andWhere($queryBuilder->expr()->eq('o.status', ':status'))
            ->setParameter(':lte_published_at', new \DateTime())
            ->setParameter(':status', Node::PUBLISHED);
        return;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }
}
