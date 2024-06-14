<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Exception\FilterValidationException;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Intersection filter must be used AFTER SearchFilter if you want to combine both.
 */
final class IntersectionFilter extends AbstractFilter
{
    public const PARAMETER = 'intersect';

    /**
     * @inheritDoc
     */
    protected function filterProperty(
        string $property,
        mixed $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        if ($property !== IntersectionFilter::PARAMETER || !is_array($value)) {
            return;
        }

        foreach ($value as $fieldName => $fieldValue) {
            if (empty($fieldName)) {
                throw new FilterValidationException([sprintf('“%s” filter must be only used with an associative array with fields as keys.', $property)]);
            }
            if ($this->isPropertyEnabled($fieldName, $resourceClass)) {
                // Allow single value intersection
                if (!is_array($fieldValue)) {
                    $fieldValue = [$fieldValue];
                }
                foreach ($fieldValue as $singleValue) {
                    [$alias, $splitFieldName] = $this->addDuplicatedJoinsForNestedProperty(
                        $fieldName,
                        'o',
                        $queryBuilder,
                        $queryNameGenerator,
                        $resourceClass,
                        Join::INNER_JOIN // Join type must be inner to filter out empty result sets
                    );
                    $placeholder = ':' . $alias . $splitFieldName;
                    $queryBuilder->andWhere($queryBuilder->expr()->eq(sprintf('%s.%s', $alias, $splitFieldName), $placeholder));
                    $queryBuilder->setParameter($placeholder, $singleValue);
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getDescription(string $resourceClass): array
    {
        $properties = $this->properties;

        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        return array_reduce(
            array_keys($properties),
            function ($carry, $property) {
                $carry[sprintf('%s[%s]', IntersectionFilter::PARAMETER, $property)] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Discriminate an existing filter with additional filtering value using a new inner join.',
                    'openapi' => [
                        'description' => 'Discriminate an existing filter with additional filtering value using a new inner join.'
                    ]
                ];
                $carry[sprintf('%s[%s][]', IntersectionFilter::PARAMETER, $property)] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Discriminate an existing filter with additional filtering value using a new inner join.',
                    'openapi' => [
                        'description' => 'Discriminate an existing filter with additional filtering value using a new inner join.'
                    ]
                ];
                return $carry;
            },
            []
        );
    }

    protected function addDuplicatedJoinsForNestedProperty(
        string $property,
        string $rootAlias,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $joinType
    ): array {
        $propertyParts = $this->splitPropertyParts($property, $resourceClass);
        $parentAlias = $rootAlias;
        $alias = null;

        foreach ($propertyParts['associations'] as $association) {
            $alias = self::addDuplicatedJoin($queryBuilder, $queryNameGenerator, $parentAlias, $association, $joinType);
            $parentAlias = $alias;
        }

        if (null === $alias) {
            throw new FilterValidationException([sprintf('Cannot add joins for property "%s" - property is not nested.', $property)]);
        }

        return [$alias, $propertyParts['field'], $propertyParts['associations']];
    }

    /**
     * Adds a join to the QueryBuilder if none exists.
     */
    public static function addDuplicatedJoin(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $association,
        string $joinType = null
    ): string {
        $associationAlias = $queryNameGenerator->generateJoinAlias($association) . uniqid();
        $query = "$alias.$association";

        if (Join::LEFT_JOIN === $joinType) {
            $queryBuilder->leftJoin($query, $associationAlias);
        } else {
            $queryBuilder->innerJoin($query, $associationAlias);
        }

        return $associationAlias;
    }
}
