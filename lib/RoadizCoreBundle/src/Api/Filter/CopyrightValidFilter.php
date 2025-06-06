<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Entity\Document;

final class CopyrightValidFilter extends AbstractFilter
{
    public const string PARAMETER = 'copyrightValid';
    public const array TRUE_VALUES = [1, '1', 'true', true, 'on', 'yes'];
    public const array FALSE_VALUES = [0, '0', 'false', false, 'off', 'no'];

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
        if (self::PARAMETER !== $property) {
            return;
        }
        if (Document::class !== $resourceClass) {
            return;
        }

        if (!in_array($value, self::TRUE_VALUES) && !in_array($value, self::FALSE_VALUES)) {
            return;
        }

        $alias = 'o';

        if (in_array($value, self::TRUE_VALUES)) {
            // Copyright MUST be valid
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull($alias.'.copyrightValidSince'),
                $queryBuilder->expr()->lte($alias.'.copyrightValidSince', ':now')
            ))->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull($alias.'.copyrightValidUntil'),
                $queryBuilder->expr()->gte($alias.'.copyrightValidUntil', ':now')
            ))->setParameter(':now', new \DateTime());

            return;
        }

        if (in_array($value, self::FALSE_VALUES)) {
            // Copyright MUST NOT be valid
            $queryBuilder->andWhere(
                $queryBuilder->expr()->orX(
                    $queryBuilder->expr()->gt($alias.'.copyrightValidSince', ':now'),
                    $queryBuilder->expr()->lt($alias.'.copyrightValidUntil', ':now')
                )
            )->setParameter(':now', new \DateTime());

            return;
        }
    }

    #[\Override]
    public function getDescription(string $resourceClass): array
    {
        return [
            self::PARAMETER => [
                'property' => self::PARAMETER,
                'type' => 'bool',
                'required' => false,
                'description' => 'Filter items for which copyright dates are valid.',
                'openapi' => new Parameter(
                    name: self::PARAMETER,
                    in: 'query',
                    description: 'Filter items for which copyright dates are valid.'
                ),
            ],
        ];
    }
}
