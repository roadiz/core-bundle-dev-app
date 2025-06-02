<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class NotFilter extends AbstractFilter
{
    public const string PARAMETER = 'not';

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
        if (self::PARAMETER !== $property || !\is_array($value)) {
            return;
        }

        foreach ($value as $property => $notValue) {
            $alias = 'o';
            $field = $property;

            if ($this->isPropertyNested($property, $resourceClass)) {
                [$alias, $field] = $this->addJoinsForNestedProperty(
                    $property,
                    $alias,
                    $queryBuilder,
                    $queryNameGenerator,
                    $resourceClass,
                    Join::INNER_JOIN
                );
            }

            $placeholder = ':'.(new AsciiSlugger())->slug($alias.'_'.$field, '_')->toString();
            if (\is_array($notValue)) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->notIn(sprintf('%s.%s', $alias, $field), $placeholder)
                );
            } else {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->neq(sprintf('%s.%s', $alias, $field), $placeholder)
                );
            }
            $queryBuilder->setParameter($placeholder, $notValue);
        }
    }

    /**
     * Gets the description of this filter for the given resource.
     *
     * Returns an array with the filter parameter names as keys and array with the following data as values:
     *   - property: the property where the filter is applied
     *   - type: the type of the filter
     *   - required: if this filter is required
     *   - strategy: the used strategy
     *   - swagger (optional): additional parameters for the path operation, e.g. 'swagger' => ['description' => 'My Description']
     * The description can contain additional data specific to a filter.
     */
    #[\Override]
    public function getDescription(string $resourceClass): array
    {
        $properties = $this->properties;

        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        return array_reduce(
            array_keys($properties),
            function ($carry, $property) {
                $carry[sprintf('%s[%s]', self::PARAMETER, $property)] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Filter items that are not equal.',
                    'openapi' => [
                        'description' => 'Filter items that are not equal.',
                    ],
                ];
                $carry[sprintf('%s[%s][]', self::PARAMETER, $property)] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Filter items that are not equal.',
                    'openapi' => [
                        'description' => 'Filter items that are not equal.',
                    ],
                ];

                return $carry;
            },
            []
        );
    }
}
