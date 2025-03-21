<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\ORM;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains;

final readonly class SimpleQueryBuilder
{
    public function __construct(private QueryBuilder $queryBuilder)
    {
    }

    public function getParameterKey(string $key): string
    {
        return \mb_strtolower(str_replace('.', '_', $key));
    }

    /**
     * @param string $prefix Property prefix including DOT
     */
    public function buildExpressionWithBinding(mixed $value, string $prefix, string $key): QueryBuilder
    {
        $this->buildExpressionWithoutBinding($value, $prefix, $key);

        return $this->bindValue($key, $value);
    }

    public function buildExpressionWithoutBinding(mixed $value, string $prefix, string $key, ?string $baseKey = null): Comparison|Func|string
    {
        if (\mb_strlen($prefix) > 0 && '.' !== \mb_substr($prefix, -\mb_strlen('.'))) {
            $prefix .= '.';
        }

        if (null === $baseKey) {
            $baseKey = $this->getParameterKey($key);
        }
        if (is_bool($value)) {
            return $this->queryBuilder->expr()->eq($prefix.$key, ':'.$baseKey);
        }
        if ('NOT NULL' === $value) {
            return $this->queryBuilder->expr()->isNotNull($prefix.$key);
        }
        if (is_array($value)) {
            /*
             * array
             *
             * ['!=', $value]
             * ['<=', $value]
             * ['<', $value]
             * ['>=', $value]
             * ['>', $value]
             * ['BETWEEN', $value, $value]
             * ['LIKE', $value]
             * ['NOT IN', [$value]]
             * [$value, $value] (IN)
             */
            if (count($value) > 1) {
                switch ($value[0]) {
                    case '!=':
                        // neq
                        return $this->queryBuilder->expr()->neq($prefix.$key, ':'.$baseKey);
                    case '<=':
                        // lte
                        return $this->queryBuilder->expr()->lte($prefix.$key, ':'.$baseKey);
                    case '<':
                        // lt
                        return $this->queryBuilder->expr()->lt($prefix.$key, ':'.$baseKey);
                    case '>=':
                        // gte
                        return $this->queryBuilder->expr()->gte($prefix.$key, ':'.$baseKey);
                    case '>':
                        // gt
                        return $this->queryBuilder->expr()->gt($prefix.$key, ':'.$baseKey);
                    case 'BETWEEN':
                        return $this->queryBuilder->expr()->between(
                            $prefix.$key,
                            ':'.$baseKey.'_1',
                            ':'.$baseKey.'_2'
                        );
                    case 'LIKE':
                        $fullKey = sprintf('LOWER(%s)', $prefix.$key);

                        return $this->queryBuilder->expr()->like(
                            $fullKey,
                            $this->queryBuilder->expr()->literal(\mb_strtolower($value[1] ?? ''))
                        );
                    case 'NOT IN':
                        return $this->queryBuilder->expr()->notIn($prefix.$key, ':'.$baseKey);
                    case JsonContains::FUNCTION_NAME:
                        // Json flat array/object contains a given value
                        return JsonContains::FUNCTION_NAME.'('.$prefix.$key.', :'.$baseKey.', \'$\') = 1';
                    case 'INSTANCE OF':
                        return $this->queryBuilder->expr()->isInstanceOf($prefix.$key, ':'.$baseKey);
                }
            }

            return $this->queryBuilder->expr()->in($prefix.$key, ':'.$baseKey);
        }
        if ($value instanceof PersistableInterface) {
            return $this->queryBuilder->expr()->eq($prefix.$key, ':'.$baseKey);
        }
        if (null === $value) {
            return $this->queryBuilder->expr()->isNull($prefix.$key);
        }

        return $this->queryBuilder->expr()->eq($prefix.$key, ':'.$baseKey);
    }

    public function bindValue(string $key, mixed $value): QueryBuilder
    {
        $key = $this->getParameterKey($key);

        if (is_bool($value) || 0 === $value) {
            return $this->queryBuilder->setParameter($key, $value);
        }
        if ('NOT NULL' == $value) {
            // param is not needed
            return $this->queryBuilder;
        }
        if (is_array($value)) {
            if (count($value) > 1) {
                switch ($value[0]) {
                    case '!=':
                    case '<=':
                    case '<':
                    case '>=':
                    case '>':
                    case 'INSTANCE OF':
                    case 'NOT IN':
                        return $this->queryBuilder->setParameter($key, $value[1]);
                    case 'BETWEEN':
                        return $this->queryBuilder->setParameter($key.'_1', $value[1])
                                                  ->setParameter($key.'_2', $value[2]);
                    case JsonContains::FUNCTION_NAME:
                        // Need to quote Json value
                        return $this->queryBuilder->setParameter($key, '"'.$value[1].'"');
                    case 'LIKE':
                        // param is set in filterBy
                        return $this->queryBuilder;
                }
            }

            return $this->queryBuilder->setParameter($key, $value);
        }
        if ($value instanceof PersistableInterface) {
            return $this->queryBuilder->setParameter($key, $value->getId());
        }
        if (null === $value) {
            return $this->queryBuilder;
        }

        return $this->queryBuilder->setParameter($key, $value);
    }

    public function joinExists(string $rootAlias, string $joinAlias): bool
    {
        if (isset($this->queryBuilder->getDQLPart('join')[$rootAlias])) {
            foreach ($this->queryBuilder->getDQLPart('join')[$rootAlias] as $join) {
                if (
                    $join instanceof Join
                    && $join->getAlias() === $joinAlias
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getRootAlias(): ?string
    {
        $fromArray = $this->getQueryBuilder()->getDQLPart('from');
        if (isset($fromArray[0]) && $fromArray[0] instanceof From) {
            return $fromArray[0]->getAlias();
        }

        return null;
    }
}
