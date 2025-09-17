<?php

declare(strict_types=1);

namespace App\Tests;

use App\GeneratedEntity\Repository\NSPageRepository;
use Doctrine\DBAL\Exception;
use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PageHierarchyTest extends KernelTestCase
{
    public function testPaths(): void
    {
        try {
            $kernel = self::bootKernel();

            $this->assertSame('test', $kernel->getEnvironment());

            /** @var UrlGeneratorInterface $urlGenerator */
            $urlGenerator = static::getContainer()->get(UrlGeneratorInterface::class);
            /** @var NSPageRepository $pageRepository */
            $pageRepository = static::getContainer()->get(NSPageRepository::class);

            $rootPage = $pageRepository->findOneBy(['title' => 'Page level 1']);
            $this->assertNotNull($rootPage);
            $this->assertSame('/', $urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $rootPage,
                ]
            ));

            $level2Page = $pageRepository->findOneBy(['title' => 'Page level 2']);
            $this->assertNotNull($level2Page);
            $this->assertSame('/page-level-2', $urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $level2Page,
                ]
            ));

            $level3Page = $pageRepository->findOneBy(['title' => 'Page level 3']);
            $this->assertNotNull($level3Page);
            $this->assertSame('/page-level-2/page-level-3', $urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $level3Page,
                ]
            ));

            $level4Page = $pageRepository->findOneBy(['title' => 'Page level 4']);
            $this->assertNotNull($level4Page);
            $this->assertSame('/page-level-2/page-level-3/page-level-4', $urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $level4Page,
                ]
            ));

            $level5Page = $pageRepository->findOneBy(['title' => 'Page level 5']);
            $this->assertNotNull($level5Page);
            $this->assertSame('/page-level-2/page-level-3/page-level-4/page-level-5', $urlGenerator->generate(
                RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                [
                    RouteObjectInterface::ROUTE_OBJECT => $level5Page,
                ]
            ));
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error: '.$e->getMessage());
        }
    }

    public function testAllParentsIdByNode(): void
    {
        try {
            $kernel = self::bootKernel();

            $this->assertSame('test', $kernel->getEnvironment());
            $nodeRepository = static::getContainer()->get(NodeRepository::class);

            if (!$nodeRepository instanceof NodeRepository) {
                $this->fail('NodeRepository not found in container');
            }

            $rootPage = $nodeRepository->findOneBy([
                'parent' => null,
                'nodeTypeName' => 'Page',
            ]);

            $this->assertNotNull($rootPage);
            $this->assertSame('Page level 1', $rootPage->getNodeSources()[0]?->getTitle());
            $this->assertCount(1, $rootPage->getChildren());

            $level2Page = $rootPage->getChildren()[0];
            $this->assertSame('Page level 2', $level2Page->getNodeSources()[0]?->getTitle());
            $this->assertCount(1, $level2Page->getChildren());

            $level3Page = $level2Page->getChildren()[0];
            $this->assertSame('Page level 3', $level3Page->getNodeSources()[0]?->getTitle());
            $this->assertCount(1, $level3Page->getChildren());

            $level4Page = $level3Page->getChildren()[0];
            $this->assertSame('Page level 4', $level4Page->getNodeSources()[0]?->getTitle());
            $this->assertCount(1, $level4Page->getChildren());

            $level5Page = $level4Page->getChildren()[0];
            $this->assertSame('Page level 5', $level5Page->getNodeSources()[0]?->getTitle());
            $this->assertCount(0, $level5Page->getChildren());

            $graphAncestors5 = [$level4Page->getId(), $level3Page->getId(), $level2Page->getId(), $rootPage->getId()];
            $this->assertSame(
                $graphAncestors5,
                $nodeRepository->findAllParentsIdByNode($level5Page)
            );

            $graphAncestors4 = [$level3Page->getId(), $level2Page->getId(), $rootPage->getId()];
            $this->assertSame(
                $graphAncestors4,
                $nodeRepository->findAllParentsIdByNode($level4Page)
            );

            $graphAncestors3 = [$level2Page->getId(), $rootPage->getId()];
            $this->assertSame(
                $graphAncestors3,
                $nodeRepository->findAllParentsIdByNode($level3Page)
            );

            $graphAncestors2 = [$rootPage->getId()];
            $this->assertSame(
                $graphAncestors2,
                $nodeRepository->findAllParentsIdByNode($level2Page)
            );

            $graphAncestors1 = [];
            $this->assertSame(
                $graphAncestors1,
                $nodeRepository->findAllParentsIdByNode($rootPage)
            );
        } catch (Exception $e) {
            $this->markTestSkipped('Database connection error: '.$e->getMessage());
        }
    }
}
