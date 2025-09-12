<?php

declare(strict_types=1);

namespace App\Tests;

use RZ\Roadiz\CoreBundle\Repository\NodeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PageHierarchyTest extends KernelTestCase
{
    public function testSomething(): void
    {
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
    }
}
