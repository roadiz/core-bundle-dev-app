<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Api\Filter\NotFilter;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Covers RZ\Roadiz\CoreBundle\Api\Filter\NotFilter:32-59 (security audit finding L8):
 * a non-allowlisted property must not be usable to build a `NOT`/`NOT IN` predicate,
 * mirroring the isPropertyEnabled() guard already present in IntersectionFilter.
 */
final class NotFilterTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    private function createQueryBuilder(): QueryBuilder
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em->createQueryBuilder()->select('o')->from(NodesSources::class, 'o');
    }

    public function testDisallowedPropertyIsIgnored(): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $originalDql = $queryBuilder->getDQL();

        $filter = new NotFilter(null, null, ['title' => null]);
        $filter->apply($queryBuilder, new QueryNameGenerator(), NodesSources::class, null, [
            'filters' => ['not' => ['metaTitle' => 'forbidden']],
        ]);

        self::assertSame($originalDql, $queryBuilder->getDQL(), 'A property missing from the allowlist must not be filterable.');
        self::assertEmpty($queryBuilder->getParameters());
    }

    public function testAllowlistedPropertyBuildsNotEqualPredicate(): void
    {
        $queryBuilder = $this->createQueryBuilder();

        $filter = new NotFilter(null, null, ['title' => null]);
        $filter->apply($queryBuilder, new QueryNameGenerator(), NodesSources::class, null, [
            'filters' => ['not' => ['title' => 'excluded']],
        ]);

        self::assertStringContainsString('o.title <>', $queryBuilder->getDQL());
        $parameter = $queryBuilder->getParameters()->first();
        self::assertNotFalse($parameter);
        self::assertSame('excluded', $parameter->getValue());
    }
}
