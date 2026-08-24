<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Api\Extension\NodeRealmExtension;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * DENY-behaviour realms must be enforced on GET /api/nodes, not just
 * /web_response_by_path, mirroring AttributeValueRealmExtension's
 * filtering pattern.
 */
final class NodeRealmExtensionTest extends KernelTestCase
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

    /**
     * @param RealmInterface[] $deniedRealms
     */
    private function createRealmResolver(array $deniedRealms = []): RealmResolverInterface
    {
        $resolver = $this->createMock(RealmResolverInterface::class);
        $resolver->method('getDeniedRealms')->willReturn($deniedRealms);

        return $resolver;
    }

    private function createRealm(int $id, string $behaviour = RealmInterface::BEHAVIOUR_DENY): RealmInterface
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getId')->willReturn($id);
        $realm->method('getBehaviour')->willReturn($behaviour);

        return $realm;
    }

    public function testNoDeniedRealmsLeavesQueryUntouched(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $originalDql = $queryBuilder->getDQL();

        $extension = new NodeRealmExtension($this->createRealmResolver());
        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        self::assertSame($originalDql, $queryBuilder->getDQL());
    }

    public function testDenyRealmExcludesGatedNodes(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $extension = new NodeRealmExtension($this->createRealmResolver([$this->createRealm(1)]));

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('o.id NOT IN', $dql);
        self::assertStringContainsString('RealmNode', $dql);
        self::assertSame([1], $queryBuilder->getParameter('deniedRealmIds')?->getValue());
    }

    public function testNonDenyBehaviourRealmsAreIgnored(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $originalDql = $queryBuilder->getDQL();
        $extension = new NodeRealmExtension($this->createRealmResolver([
            $this->createRealm(1, RealmInterface::BEHAVIOUR_HIDE_BLOCKS),
        ]));

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        self::assertSame($originalDql, $queryBuilder->getDQL(), 'Only BEHAVIOUR_DENY realms should filter Node queries.');
    }

    public function testUnrelatedResourceClassIsIgnored(): void
    {
        $queryBuilder = $this->createQueryBuilder(AttributeValue::class);
        $originalDql = $queryBuilder->getDQL();
        $extension = new NodeRealmExtension($this->createRealmResolver([$this->createRealm(1)]));

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), AttributeValue::class);

        self::assertSame($originalDql, $queryBuilder->getDQL());
    }

    public function testApplyToItemMutatesQueryBuilderLikeApplyToCollection(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $extension = new NodeRealmExtension($this->createRealmResolver([$this->createRealm(1)]));

        $extension->applyToItem($queryBuilder, new QueryNameGenerator(), Node::class, ['id' => 1]);

        self::assertStringContainsString('o.id NOT IN', $queryBuilder->getDQL());
    }
}
