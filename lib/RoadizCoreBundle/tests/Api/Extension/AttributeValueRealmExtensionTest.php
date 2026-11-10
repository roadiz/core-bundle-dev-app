<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use RZ\Roadiz\CoreBundle\Api\Extension\AttributeValueRealmExtension;
use RZ\Roadiz\CoreBundle\Entity\AttributeValue;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Covers RZ\Roadiz\CoreBundle\Api\Extension\AttributeValueRealmExtension:47-62,
 * the DQL mutation that hides realm-gated AttributeValue rows from users who
 * lack access to them.
 *
 * Uses a real Doctrine QueryBuilder (built from the test container's EntityManager)
 * so getDQL() reflects the actual mutation instead of a hand-rolled string.
 */
final class AttributeValueRealmExtensionTest extends KernelTestCase
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

    private function createSecurity(bool $hasAttributeRole, bool $isAnonymous): Security
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnCallback(
            static fn (mixed $attribute, mixed $subject = null): bool => match ($attribute) {
                'ROLE_ACCESS_NODE_ATTRIBUTES' => $hasAttributeRole,
                'IS_ANONYMOUS' => $isAnonymous,
                default => false,
            }
        );

        return $security;
    }

    /**
     * @param RealmInterface[] $grantedRealms
     */
    private function createRealmResolver(array $grantedRealms = []): RealmResolverInterface
    {
        $resolver = $this->createMock(RealmResolverInterface::class);
        $resolver->method('getGrantedRealms')->willReturn($grantedRealms);

        return $resolver;
    }

    private function createRealm(int $id): RealmInterface
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getId')->willReturn($id);

        return $realm;
    }

    public function testAnonymousUserOnlySeesNullRealmAttributeValues(): void
    {
        $queryBuilder = $this->createQueryBuilder(AttributeValue::class);
        $extension = new AttributeValueRealmExtension(
            $this->createSecurity(hasAttributeRole: false, isAnonymous: true),
            $this->createRealmResolver(),
        );

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), AttributeValue::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('o.realm IS NULL', $dql);
        self::assertStringNotContainsString(':realmIds', $dql);
    }

    public function testGrantedUserSeesNullRealmOrGrantedRealmAttributeValues(): void
    {
        $queryBuilder = $this->createQueryBuilder(AttributeValue::class);
        $extension = new AttributeValueRealmExtension(
            $this->createSecurity(hasAttributeRole: false, isAnonymous: false),
            $this->createRealmResolver([$this->createRealm(1), $this->createRealm(2)]),
        );

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), AttributeValue::class);

        $dql = $queryBuilder->getDQL();
        self::assertStringContainsString('o.realm IS NULL', $dql);
        self::assertStringContainsString('o.realm IN', $dql);
        self::assertSame([1, 2], $queryBuilder->getParameter('realmIds')?->getValue());
    }

    public function testRoleAccessNodeAttributesBypassesRealmFiltering(): void
    {
        $queryBuilder = $this->createQueryBuilder(AttributeValue::class);
        $originalDql = $queryBuilder->getDQL();

        $extension = new AttributeValueRealmExtension(
            $this->createSecurity(hasAttributeRole: true, isAnonymous: false),
            $this->createRealmResolver(),
        );

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), AttributeValue::class);

        self::assertSame($originalDql, $queryBuilder->getDQL(), 'ROLE_ACCESS_NODE_ATTRIBUTES holders must bypass realm filtering entirely.');
    }

    public function testUnrelatedResourceClassIsIgnored(): void
    {
        $queryBuilder = $this->createQueryBuilder(Node::class);
        $originalDql = $queryBuilder->getDQL();

        $extension = new AttributeValueRealmExtension(
            $this->createSecurity(hasAttributeRole: false, isAnonymous: true),
            $this->createRealmResolver(),
        );

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Node::class);

        self::assertSame($originalDql, $queryBuilder->getDQL(), 'Only AttributeValue queries should be mutated.');
    }

    public function testApplyToItemMutatesQueryBuilderLikeApplyToCollection(): void
    {
        $queryBuilder = $this->createQueryBuilder(AttributeValue::class);
        $extension = new AttributeValueRealmExtension(
            $this->createSecurity(hasAttributeRole: false, isAnonymous: true),
            $this->createRealmResolver(),
        );

        $extension->applyToItem($queryBuilder, new QueryNameGenerator(), AttributeValue::class, ['id' => 1]);

        self::assertStringContainsString('o.realm IS NULL', $queryBuilder->getDQL());
    }
}
