<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final readonly class NodesSourcesQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    use NodesSourcesStatusExtensionTrait;

    public function __construct(
        private PreviewResolverInterface $previewResolver,
        private string $generatedEntityNamespacePattern = '#^App\\\GeneratedEntity\\\NS(?:[a-zA-Z]+)$#',
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

    /**
     * Should be identical to NodesSourcesRepository::alterQueryBuilderWithAuthorizationChecker().
     */
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
            $queryBuilder->andWhere($queryBuilder->expr()->isInstanceOf('o', $resourceClass));
        }

        $this->alterQueryBuilderWithStatus($queryBuilder, 'o');
    }
}
