<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use Symfony\Component\Security\Core\Security;

final class AttributeValueRealmExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private Security $security,
        private RealmResolverInterface $realmResolver
    ) {
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== AttributeValue::class || $this->security->isGranted('ROLE_ACCESS_NODE_ATTRIBUTES')) {
            return;
        }

        /*
         * Filter out all attribute values requiring a realm for anonymous users.
         */
        $rootAlias = $queryBuilder->getRootAliases()[0];
        if ($this->security->isGranted('IS_ANONYMOUS')) {
            $queryBuilder->andWhere($queryBuilder->expr()->isNull(sprintf('%s.realm', $rootAlias)));
            return;
        }

        /*
         * Filter all attribute values requiring a granted realm or no realm for current user.
         */
        $queryBuilder->andWhere($queryBuilder->expr()->orX(
            $queryBuilder->expr()->isNull(sprintf('%s.realm', $rootAlias)),
            $queryBuilder->expr()->in(
                sprintf('%s.realm', $rootAlias),
                ':realmIds'
            )
        ))->setParameter('realmIds', $this->getGrantedRealmIds());
    }

    private function getGrantedRealmIds(): array
    {
        return array_map(
            fn (RealmInterface $realm) => $realm->getId(),
            array_filter($this->realmResolver->getGrantedRealms())
        );
    }
}
