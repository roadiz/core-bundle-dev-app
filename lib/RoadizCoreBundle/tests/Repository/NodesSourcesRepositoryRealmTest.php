<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Model\RealmInterface;
use RZ\Roadiz\CoreBundle\Preview\PreviewResolverInterface;
use RZ\Roadiz\CoreBundle\Realm\RealmResolverInterface;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * DENY-behaviour realms gate public delivery, not the backoffice: a back-end user
 * must still reach a gated node source, otherwise Rozier answers 404 on every node
 * behind a plain-password realm, which can never be granted from the admin.
 */
final class NodesSourcesRepositoryRealmTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    private function createRepository(bool $isBackendUser, array $deniedRealms): NodesSourcesRepository
    {
        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturnCallback(
            fn (mixed $attribute): bool => 'ROLE_BACKEND_USER' === $attribute && $isBackendUser
        );

        $realmResolver = $this->createMock(RealmResolverInterface::class);
        $realmResolver->method('getDeniedRealms')->willReturn($deniedRealms);

        /** @var ManagerRegistry $registry */
        $registry = static::getContainer()->get(ManagerRegistry::class);
        /** @var PreviewResolverInterface $previewResolver */
        $previewResolver = static::getContainer()->get(PreviewResolverInterface::class);
        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = static::getContainer()->get(EventDispatcherInterface::class);

        $repository = new NodesSourcesRepository($registry, $previewResolver, $dispatcher, $security);
        $repository->setRealmResolver($realmResolver);

        return $repository;
    }

    private function createDenyRealm(int $id): RealmInterface
    {
        $realm = $this->createMock(RealmInterface::class);
        $realm->method('getId')->willReturn($id);
        $realm->method('getBehaviour')->willReturn(RealmInterface::BEHAVIOUR_DENY);

        return $realm;
    }

    private function filterByDeniedRealms(NodesSourcesRepository $repository): QueryBuilder
    {
        /** @var EntityManagerInterface $em */
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $queryBuilder = $em->createQueryBuilder()->select('ns')->from(NodesSources::class, 'ns');

        $method = new \ReflectionMethod($repository, 'filterByDeniedRealms');
        $method->invoke($repository, $queryBuilder);

        return $queryBuilder;
    }

    public function testDenyRealmExcludesGatedSourcesForAnonymousUsers(): void
    {
        $queryBuilder = $this->filterByDeniedRealms(
            $this->createRepository(isBackendUser: false, deniedRealms: [$this->createDenyRealm(1)])
        );

        self::assertStringContainsString('NOT IN', $queryBuilder->getDQL());
        self::assertSame([1], $queryBuilder->getParameter('deniedRealmIds')?->getValue());
    }

    public function testBackendUserIsNotFilteredByDeniedRealms(): void
    {
        $queryBuilder = $this->filterByDeniedRealms(
            $this->createRepository(isBackendUser: true, deniedRealms: [$this->createDenyRealm(1)])
        );

        self::assertStringNotContainsString('NOT IN', $queryBuilder->getDQL());
        self::assertNull($queryBuilder->getParameter('deniedRealmIds'));
    }
}
