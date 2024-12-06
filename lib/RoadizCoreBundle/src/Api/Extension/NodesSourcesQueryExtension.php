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
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final readonly class NodesSourcesQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private PreviewResolverInterface $previewResolver,
        private string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
    ) {
    }

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
            NodesSources::class !== $resourceClass
            && 0 === preg_match($this->generatedEntityNamespacePattern, $resourceClass)
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
                ->andWhere($queryBuilder->expr()->lte($alias.'.status', ':status'))
                ->setParameter(':status', NodeStatus::PUBLISHED);

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
            ->andWhere($queryBuilder->expr()->eq($alias.'.status', ':status'))
            ->setParameter(':lte_published_at', new \DateTime())
            ->setParameter(':status', NodeStatus::PUBLISHED);

        return;
    }
}
