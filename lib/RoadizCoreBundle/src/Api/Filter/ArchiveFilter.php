<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Filter;

use ApiPlatform\Doctrine\Common\PropertyHelperTrait;
use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

final class ArchiveFilter extends AbstractFilter
{
    use PropertyHelperTrait;

    public const string PARAMETER_ARCHIVE = 'archive';

    /**
     * Determines whether the given property refers to a date field.
     */
    protected function isDateField(string $property, string $resourceClass): bool
    {
        $type = $this->getDoctrineFieldType($property, $resourceClass);
        if (null === $type) {
            return false;
        }

        return \in_array($type, \array_keys(DateFilter::DOCTRINE_DATE_TYPES), true);
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
        // Expect $values to be an array having the period as keys and the date value as values
        if (
            !$this->isPropertyEnabled($property, $resourceClass)
            || !$this->isPropertyMapped($property, $resourceClass)
            || !$this->isDateField($property, $resourceClass)
            || !isset($value[self::PARAMETER_ARCHIVE])
        ) {
            return;
        }

        $alias = $queryBuilder->getRootAliases()[0];
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

        if (!is_string($value[self::PARAMETER_ARCHIVE])) {
            throw new InvalidArgumentException(sprintf('“%s” filter must be only used with a string value.', self::PARAMETER_ARCHIVE));
        }

        $this->singleArchiveFilter(
            $queryBuilder,
            $queryNameGenerator,
            $alias,
            $field,
            $value[self::PARAMETER_ARCHIVE],
        );
    }

    protected function singleArchiveFilter(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $alias,
        string $field,
        string $value,
    ): void {
        [$startDate, $endDate] = $this->normalizeFilteringDates($value);

        $valueParameter = $queryNameGenerator->generateParameterName($field);
        $queryBuilder->andWhere($queryBuilder->expr()->isNotNull(sprintf('%s.%s', $alias, $field)))
            ->andWhere($queryBuilder->expr()->between(
                sprintf('%s.%s', $alias, $field),
                ':'.$valueParameter.'Start',
                ':'.$valueParameter.'End'
            ))
            ->setParameter($valueParameter.'Start', $startDate)
            ->setParameter($valueParameter.'End', $endDate);
    }

    /**
     * Support archive parameter with year or year-month.
     *
     * @return array{\DateTime, \DateTime}
     *
     * @throws \Exception
     */
    protected function normalizeFilteringDates(string $value): array
    {
        /*
         * Support archive parameter with year or year-month
         */
        if (preg_match('#^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$#', $value) > 0) {
            $startDate = new \DateTime($value.' 00:00:00');
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P1D'));

            return [$startDate, $this->limitEndDate($endDate)];
        } elseif (preg_match('#^[0-9]{4}\-[0-9]{2}$#', $value) > 0) {
            $startDate = new \DateTime($value.'-01 00:00:00');
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P1M'));

            return [$startDate, $this->limitEndDate($endDate)];
        } elseif (preg_match('#^[0-9]{4}$#', $value) > 0) {
            $startDate = new \DateTime($value.'-01-01 00:00:00');
            $endDate = clone $startDate;
            $endDate->add(new \DateInterval('P1Y'));

            return [$startDate, $this->limitEndDate($endDate)];
        }

        throw new InvalidArgumentException(sprintf('“%s” filter must be a valid archive specification.', self::PARAMETER_ARCHIVE));
    }

    protected function limitEndDate(\DateTime $endDate): \DateTime
    {
        $now = new \DateTime();
        if ($endDate > $now) {
            return $now;
        }

        return $endDate->sub(new \DateInterval('PT1S'));
    }

    #[\Override]
    public function getDescription(string $resourceClass): array
    {
        $description = [];

        $properties = $this->getProperties();
        if (null === $properties) {
            $properties = array_fill_keys($this->getClassMetadata($resourceClass)->getFieldNames(), null);
        }

        foreach ($properties as $property => $nullManagement) {
            if (!$this->isPropertyMapped($property, $resourceClass) || !$this->isDateField($property, $resourceClass)) {
                continue;
            }

            $description += $this->getFilterDescription($property);
        }

        return $description;
    }

    /**
     * Gets filter description.
     */
    protected function getFilterDescription(string $property): array
    {
        $propertyName = $this->normalizePropertyName($property);

        return [
            sprintf('%s[%s]', $propertyName, self::PARAMETER_ARCHIVE) => [
                'property' => $propertyName,
                'type' => 'string',
                'required' => false,
            ],
        ];
    }
}
