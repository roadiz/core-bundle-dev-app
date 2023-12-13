<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final class AttributeValueQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
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
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $resourceClass);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->apply($queryBuilder, $resourceClass);
    }

    private function apply(
        QueryBuilder $queryBuilder,
        string $resourceClass
    ): void {
        if (
            $resourceClass !== AttributeValue::class
        ) {
            return;
        }

        $parts = $queryBuilder->getDQLPart('join');
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if (!\is_array($parts) || !isset($parts[$rootAlias])) {
            return;
        }

        $existingNodeJoin = QueryBuilderHelper::getExistingJoin($queryBuilder, 'o', 'node');
        if (null === $existingNodeJoin || !$existingNodeJoin->getAlias()) {
            return;
        }

        if ($this->previewResolver->isPreview()) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte($existingNodeJoin->getAlias() . '.status', ':status'))
                ->setParameter(':status', Node::PUBLISHED);
            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq($existingNodeJoin->getAlias() . '.status', ':status'))
            ->setParameter(':status', Node::PUBLISHED);
        return;
    }
}
