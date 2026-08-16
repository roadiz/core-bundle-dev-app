<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Api\Extension\NodesSourcesQueryExtension;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Covers RZ\Roadiz\CoreBundle\Api\Extension\NodesSourcesQueryExtension: always
 * excludes shadow nodes (via a join to `node`), filters by status/publication
 * date depending on preview mode, and adds an INSTANCE OF clause for generated
 * NodesSources subclasses (App\GeneratedEntity\NS*).
 *
 * IMPORTANT (security audit finding H2): unlike AttributeValueRealmExtension,
 * this extension applies NO Realm-based filtering at all. A NodesSources
 * belonging to a node under a DENY realm is NOT excluded from /api/nodes_sources
 * collections. This is a known gap, not a bug in this test — see
 * lib/RoadizCoreBundle/tests/Api/Extension/AttributeValueRealmExtensionTest.php
 * for the realm-filtering behaviour that AttributeValue gets but NodesSources
 * doesn't, and tests/RealmApiTest.php for the functional placeholder covering
 * the desired /api/nodes_sources behaviour once H2 is fixed.
 *
 * Uses a real Doctrine QueryBuilder (built from the test container's EntityManager)
 * so getDQL() reflects the actual mutation instead of a hand-rolled string.
 */
final class NodesSourcesQueryExtensionTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    private function createQueryBuilder(string $resourceClass): QueryBuilder
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);

        return $em->createQueryBuilder()->select('o')->from($resourceClass, 'o');
    }

    private function createPreviewResolver(bool $isPreview): PreviewResolverInterface
    {
        $resolver = $this->createMock(PreviewResolverInterface::class);
        $resolver->method('isPreview')->willReturn($isPreview);

        return $resolver;
    }

    public function testUnrelatedResourceClassIsIgnored(): void
    {
        $queryBuilder = $this->createQueryBuilder(AttributeValue::class);
        $originalDql = $queryBuilder->getDQL();

        $extension = new NodesSourcesQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), AttributeValue::class);

        self::assertSame($originalDql, $queryBuilder->getDQL(), 'Only NodesSources (or generated subclasses) queries should be mutated.');
    }

    public function testAlwaysExcludesShadowNodesViaJoin(): void
    {
        $queryBuilder = $this->createQueryBuilder(NodesSources::class);
        $extension = new NodesSourcesQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), NodesSources::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('INNER JOIN o.node', $dql);
        self::assertMatchesRegularExpression('/node_a\d+\.shadow = :shadow/', $dql);
        self::assertFalse($queryBuilder->getParameter(':shadow')?->getValue());
    }

    public function testPreviewModeOnlyFiltersByStatusLte(): void
    {
        $queryBuilder = $this->createQueryBuilder(NodesSources::class);
        $extension = new NodesSourcesQueryExtension($this->createPreviewResolver(true));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), NodesSources::class);

        $dql = $queryBuilder->getDQL();
        self::assertMatchesRegularExpression('/node_a\d+\.status <= :status/', $dql);
        // Preview mode returns early: no publication-date filter on `o`.
        self::assertStringNotContainsString('publishedAt', $dql);
    }

    /**
     * MERGE-INTO-DEVELOP TODO: `develop` adds an `unpublishedAt` column to NodesSources
     * and its non-preview branch now also filters
     * `o.unpublishedAt > :gt_unpublished_at OR o.unpublishedAt IS NULL`. This test still
     * passes as-is after merging (its assertions don't check for absence of other
     * clauses), but it will no longer fully describe the real filtering behaviour.
     * Add assertions for that OR clause and the `:gt_unpublished_at` parameter here.
     */
    public function testNonPreviewModeFiltersByExactStatusAndPublicationDate(): void
    {
        $queryBuilder = $this->createQueryBuilder(NodesSources::class);
        $extension = new NodesSourcesQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), NodesSources::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('o.publishedAt <= :lte_published_at', $dql);
        self::assertMatchesRegularExpression('/node_a\d+\.status = :status/', $dql);
        self::assertStringNotContainsString('.status <=', $dql);
    }

    public function testGeneratedEntitySubclassAddsInstanceOfClause(): void
    {
        // Not a real class: applyToCollection() only builds the DQL string here
        // (isInstanceOf is never parsed/executed in this test), so a fake FQCN
        // matching the extension's namespace pattern is enough and keeps this
        // RoadizCoreBundle test free of any dependency on the dev app's
        // App\GeneratedEntity\* classes.
        $fakeGeneratedClass = 'App\\GeneratedEntity\\NSFake';

        $queryBuilder = $this->createQueryBuilder(NodesSources::class);
        $extension = new NodesSourcesQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), $fakeGeneratedClass);

        self::assertStringContainsString('o INSTANCE OF '.$fakeGeneratedClass, $queryBuilder->getDQL());
    }

    public function testApplyToItemMutatesQueryBuilderLikeApplyToCollection(): void
    {
        $queryBuilder = $this->createQueryBuilder(NodesSources::class);
        $extension = new NodesSourcesQueryExtension($this->createPreviewResolver(true));
        $extension->applyToItem($queryBuilder, new QueryNameGenerator(), NodesSources::class, ['id' => 1]);

        self::assertMatchesRegularExpression('/node_a\d+\.status <= :status/', $queryBuilder->getDQL());
    }
}
