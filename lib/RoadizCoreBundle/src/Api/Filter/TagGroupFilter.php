<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\CoreBundle\Entity\Node;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class TagGroupFilter extends AbstractFilter
{
    public const PROPERTY = 'tagGroup';

    public function __construct(
        ManagerRegistry $managerRegistry,
        ?LoggerInterface $logger = null,
        ?array $properties = null,
        ?NameConverterInterface $nameConverter = null,
    ) {
        parent::__construct($managerRegistry, $logger, $properties, $nameConverter);
    }

    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if (self::PROPERTY !== $property) {
            return;
        }
        if (!\is_array($value)) {
            return;
        }

        /*
         * Convert comma separated tag identifiers to sub-arrays
         */
        $normalizedValue = [];

        foreach ($value as $group) {
            if (!\is_array($group)) {
                $group = explode(',', $group);
            }
            $normalizedValue[] = array_filter(array_map('trim', $group));
        }

        if (Node::class !== $resourceClass) {
            /*
             * If entity is node source, we need to join node
             */
            if (null === $nodeJoin = $this->joinExists($queryBuilder)) {
                $nodeJoin = 'node';
                $queryBuilder->innerJoin('o.node', $nodeJoin);
            }

            $subQueryBuilder = $this->managerRegistry->getRepository(Node::class)->createQueryBuilder('n');
            $subQueryBuilder->select('n.id');
            $this->filterByTagNames($subQueryBuilder, $normalizedValue, 'n');
            $queryBuilder->andWhere($queryBuilder->expr()->in($nodeJoin.'.id', $subQueryBuilder->getDQL()));
        } else {
            /*
             * For each tag group we create an inner-join
             */
            $this->filterByTagNames($queryBuilder, $normalizedValue, 'o');
        }

        // Apply the tag names filter on root query builder
        $this->setTagNamesParameters($queryBuilder, $normalizedValue);
    }

    public function getDescription(string $resourceClass): array
    {
        $carry = [];
        $carry[self::PROPERTY.'[]'] = [
            'property' => self::PROPERTY.'[]',
            'type' => Type::BUILTIN_TYPE_ARRAY,
            'required' => false,
            'description' => 'Filter entities by tag name groups (comma separated). Inside groups filter use OR, between each groups filter use AND.',
            'openapi' => new Parameter(
                name: self::PROPERTY.'[]',
                in: 'query',
                description: 'Filter entities by tag name groups (comma separated). Inside groups filter use OR, between each groups filter use AND.',
                allowEmptyValue: false,
                explode: true,
                example: 'tag-1,tag-2&'.self::PROPERTY.'[]=tag-3,tag-4',
            ),
        ];

        return $carry;
    }

    private function filterByTagNames(QueryBuilder $queryBuilder, array $groups, string $nodeAlias): void
    {
        /*
         * For each tag group we create an inner-join
         */
        foreach ($groups as $i => $tagGroup) {
            $nodesTagsJoin = self::PROPERTY.'_nodesTags_'.$i;
            $joinAlias = self::PROPERTY.'_tag_'.$i;
            $parameterName = 'p_'.$joinAlias;
            $queryBuilder
                ->innerJoin($nodeAlias.'.nodesTags', $nodesTagsJoin)
                ->innerJoin($nodesTagsJoin.'.tag', $joinAlias)
                ->andWhere($queryBuilder->expr()->in(
                    $joinAlias.'.tagName',
                    ':'.$parameterName
                ))
            ;
        }
    }

    private function setTagNamesParameters(QueryBuilder $queryBuilder, array $groups): void
    {
        /*
         * For each tag group we create an inner-join
         */
        foreach ($groups as $i => $tagGroup) {
            $joinAlias = self::PROPERTY.'_tag_'.$i;
            $parameterName = 'p_'.$joinAlias;
            $queryBuilder->setParameter($parameterName, $tagGroup);
        }
    }

    private function joinExists(QueryBuilder $queryBuilder): ?string
    {
        /** @var Join[][] $joinDqlParts */
        $joinDqlParts = $queryBuilder->getDQLParts()['join'];
        foreach ($joinDqlParts as $joins) {
            foreach ($joins as $join) {
                if ('o.node' === $join->getJoin()) {
                    return $join->getAlias();
                }
            }
        }

        return null;
    }
}
