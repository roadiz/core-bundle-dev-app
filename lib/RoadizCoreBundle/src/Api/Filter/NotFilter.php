<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\AbstractContextAwareFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class NotFilter extends AbstractContextAwareFilter
{
    public const PARAMETER = 'not';

    /**
     * Passes a property through the filter.
     *
     * @param string $property
     * @param mixed $value
     * @param QueryBuilder $queryBuilder
     * @param QueryNameGeneratorInterface $queryNameGenerator
     * @param string $resourceClass
     * @param string|null $operationName
     * @throws \Exception
     */
    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        string $operationName = null
    ): void {
        if ($property !== self::PARAMETER || !\is_array($value)) {
            return;
        }

        foreach ($value as $property => $notValue) {
            $alias = 'o';
            $field = $property;

            if ($this->isPropertyNested($property, $resourceClass)) {
                list($alias, $field) = $this->addJoinsForNestedProperty($property, $alias, $queryBuilder, $queryNameGenerator);
            }

            $placeholder = ':' . (new AsciiSlugger())->slug($alias . '_' . $field, '_')->toString();
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
     *
     * @param string $resourceClass
     *
     * @return array
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
                $carry[sprintf('%s[%s]', self::PARAMETER, $property)] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Filter items that are not equal.',
                    'openapi' => [
                        'description' => 'Filter items that are not equal.'
                    ]
                ];
                $carry[sprintf('%s[%s][]', self::PARAMETER, $property)] = [
                    'property' => $property,
                    'type' => 'string',
                    'required' => false,
                    'description' => 'Filter items that are not equal.',
                    'openapi' => [
                        'description' => 'Filter items that are not equal.'
                    ]
                ];
                return $carry;
            },
            []
        );
    }
}
