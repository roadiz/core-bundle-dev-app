<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Common\PropertyHelperTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class NodeTypeReachableFilter extends AbstractFilter
{
    use PropertyHelperTrait;

    public const string FILTER_COMPAT = 'node.nodeType.reachable';
    public const string FILTER = 'reachable';

    public const array TRUE_VALUES = [1, '1', 'true', true, 'on', 'yes'];
    public const array FALSE_VALUES = [0, '0', 'false', false, 'off', 'no'];

    public function __construct(
        private readonly NodeTypes $nodeTypesBag,
        ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    #[\Override]
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (
            (self::FILTER !== $property && self::FILTER_COMPAT !== $property)
            || (!in_array($value, self::TRUE_VALUES) && !in_array($value, self::FALSE_VALUES))
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
        $reachable = in_array($value, self::TRUE_VALUES);

        if (Node::class === $resourceClass) {
            $nodeTypes = array_map(
                fn (NodeTypeInterface $type) => $type->getName(),
                $this->nodeTypesBag->allReachable($reachable)
            );
            $queryBuilder
                ->andWhere($queryBuilder->expr()->in(sprintf('%s.nodeTypeName', $alias), ':nodeTypeNames'))
                ->setParameter(':nodeTypeNames', $nodeTypes);

            return;
        }
        /*
         * For all nodeSources, we need to join node to get nodeTypeName.
         */
        $nodeTypeClasses = array_map(
            fn (NodeTypeInterface $type) => $type->getSourceEntityFullQualifiedClassName(),
            $this->nodeTypesBag->allReachable($reachable)
        );

        $queryBuilder->andWhere($queryBuilder->expr()->orX(
            ...array_map(
                fn (string $nodeTypeClass) => $queryBuilder->expr()->isInstanceOf($alias, $nodeTypeClass),
                $nodeTypeClasses
            )
        ));
    }

    /**
     * Gets filter description.
     */
    #[\Override]
    public function getDescription(string $property): array
    {
        return [
            self::FILTER => [
                'property' => self::FILTER,
                'description' => 'Filter items when their node-type is reachable or not.',
                'type' => 'boolean',
                'required' => false,
            ],
            self::FILTER_COMPAT => [
                'property' => self::FILTER_COMPAT,
                'description' => sprintf('Filter items when their node-type is reachable or not. **Deprecated: Use *%s* instead.**', self::FILTER),
                'deprecated' => sprintf('The %s filter is deprecated, use %s instead.', self::FILTER_COMPAT, self::FILTER),
                'type' => 'boolean',
                'required' => false,
            ],
        ];
    }
}
