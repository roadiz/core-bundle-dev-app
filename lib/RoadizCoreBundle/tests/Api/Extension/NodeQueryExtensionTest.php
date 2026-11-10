<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Api\Extension\NodeQueryExtension;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Covers RZ\Roadiz\CoreBundle\Api\Extension\NodeQueryExtension: always excludes
 * shadow nodes, and filters by status/publication date depending on preview mode.
 *
 * Uses a real Doctrine QueryBuilder (built from the test container's EntityManager)
 * so getDQL() reflects the actual mutation instead of a hand-rolled string.
 */
final class NodeQueryExtensionTest extends KernelTestCase
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

        $extension = new NodeQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), AttributeValue::class);

        self::assertSame($originalDql, $queryBuilder->getDQL(), 'Only Node queries should be mutated.');
    }

    public function testAlwaysExcludesShadowNodes(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $extension = new NodeQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('o.shadow = :shadow', $dql);
        self::assertFalse($queryBuilder->getParameter(':shadow')?->getValue());
    }

    public function testPreviewModeOnlyFiltersByStatusLte(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $extension = new NodeQueryExtension($this->createPreviewResolver(true));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('o.status <= :status', $dql);
        // Preview mode returns early: no join and no publication-date filter.
        self::assertStringNotContainsString('JOIN', $dql);
        self::assertStringNotContainsString('publishedAt', $dql);
    }

    public function testNonPreviewModeFiltersByExactStatusAndPublicationDate(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $extension = new NodeQueryExtension($this->createPreviewResolver(false));
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('INNER JOIN o.nodeSources', $dql);
        self::assertStringContainsString('o.status = :status', $dql);
        self::assertStringNotContainsString('o.status <=', $dql);
        self::assertMatchesRegularExpression('/nodeSources_a\d+\.publishedAt <= :lte_published_at/', $dql);
    }

    public function testApplyToItemMutatesQueryBuilderLikeApplyToCollection(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $extension = new NodeQueryExtension($this->createPreviewResolver(true));
        $extension->applyToItem($queryBuilder, new QueryNameGenerator(), Node::class, ['id' => 1]);

        self::assertStringContainsString('o.status <= :status', $queryBuilder->getDQL());
    }
}
