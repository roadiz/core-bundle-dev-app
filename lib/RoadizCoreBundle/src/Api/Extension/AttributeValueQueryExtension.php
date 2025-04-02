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
use RZ\Roadiz\CoreBundle\Enum\NodeStatus;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;

final readonly class AttributeValueQueryExtension implements QueryItemExtensionInterface, QueryCollectionExtensionInterface
{
    public function __construct(
        private PreviewResolverInterface $previewResolver,
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
        $this->apply($queryBuilder, $resourceClass);
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->apply($queryBuilder, $resourceClass);
    }

    private function apply(
        QueryBuilder $queryBuilder,
        string $resourceClass,
    ): void {
        if (
            AttributeValue::class !== $resourceClass
        ) {
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        /**
         * AttributeValue is always linked to a Node.
         * We need to join Node to filter by its status.
         */
        $existingNodeJoin = QueryBuilderHelper::getExistingJoin($queryBuilder, 'o', 'node');
        if (null === $existingNodeJoin || !$existingNodeJoin->getAlias()) {
            $queryBuilder->leftJoin($rootAlias.'.node', 'node');
            $joinAlias = 'node';
        } else {
            $joinAlias = $existingNodeJoin->getAlias();
        }

        if ($this->previewResolver->isPreview()) {
            $queryBuilder
                ->andWhere($queryBuilder->expr()->lte($joinAlias.'.status', ':status'))
                ->setParameter(':status', NodeStatus::PUBLISHED);

            return;
        }

        $queryBuilder
            ->andWhere($queryBuilder->expr()->eq($joinAlias.'.status', ':status'))
            ->setParameter(':status', NodeStatus::PUBLISHED);

        return;
    }
}
