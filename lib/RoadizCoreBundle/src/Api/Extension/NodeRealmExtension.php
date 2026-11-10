<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\RealmNode;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;

/**
 * Excludes nodes gated by a DENY-behaviour Realm the current user isn't
 * granted, mirroring AttributeValueRealmExtension's filtering pattern.
 * DENY enforcement previously only ran on /web_response_by_path, leaking
 * realm-protected nodes through GET /api/nodes.
 */
final readonly class NodeRealmExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private RealmResolverInterface $realmResolver,
    ) {
    }

    #[\Override]
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
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
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if (Node::class !== $resourceClass) {
            return;
        }

        $deniedRealmIds = $this->getDeniedRealmIds();
        if ([] === $deniedRealmIds) {
            return;
        }

        $queryBuilder->andWhere($queryBuilder->expr()->notIn(
            'o.id',
            sprintf('SELECT IDENTITY(rn.node) FROM %s rn WHERE rn.realm IN (:deniedRealmIds)', RealmNode::class)
        ))->setParameter('deniedRealmIds', $deniedRealmIds);
    }

    private function getDeniedRealmIds(): array
    {
        return array_values(array_map(
            fn (RealmInterface $realm) => $realm->getId(),
            array_filter(
                $this->realmResolver->getDeniedRealms(),
                fn (RealmInterface $realm) => RealmInterface::BEHAVIOUR_DENY === $realm->getBehaviour()
            )
        ));
    }
}
