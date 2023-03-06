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
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final class NodesSourcesQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    private PreviewResolverInterface $previewResolver;
    private string $generatedEntityNamespacePattern;

    public function __construct(
        PreviewResolverInterface $previewResolver,
        string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#'
    ) {
        $this->previewResolver = $previewResolver;
        $this->generatedEntityNamespacePattern = $generatedEntityNamespacePattern;
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

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        $this->apply($queryBuilder, $queryNameGenerator, $resourceClass, $operationName);
    }

    private function apply(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if (
            $resourceClass !== NodesSources::class &&
            preg_match($this->generatedEntityNamespacePattern, $resourceClass) === 0
        ) {
            return;
        }

        if (preg_match($this->generatedEntityNamespacePattern, $resourceClass) > 0) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->isInstanceOf('o', $resourceClass));
        }

        if ($this->previewResolver->isPreview()) {
            $alias = QueryBuilderHelper::addJoinOnce(
                $queryBuilder,
                $queryNameGenerator,
                'o',
                'node',
                Join::INNER_JOIN
            );
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte($alias . '.status', ':status'))
                ->setParameter(':status', Node::PUBLISHED);
            return;
        }

        $alias = QueryBuilderHelper::addJoinOnce(
            $queryBuilder,
            $queryNameGenerator,
            'o',
            'node',
            Join::INNER_JOIN
        );
        $queryBuilder
            ->andWhere($queryBuilder->expr()->lte('o.publishedAt', ':lte_published_at'))
            ->andWhere($queryBuilder->expr()->eq($alias . '.status', ':status'))
            ->setParameter(':lte_published_at', new \DateTime())
            ->setParameter(':status', Node::PUBLISHED);
        return;
    }
}
