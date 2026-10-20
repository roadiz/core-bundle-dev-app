<?php

declare(strict_types=1);

namespace App\Tests;

use Psr\Log\LoggerInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\CoreBundle\Bag\NodeTypes;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\NodeType\DocumentsDtoSettersCacheWarmer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DocumentsDtoSettersCacheWarmerTest extends KernelTestCase
{
    public function testWarnsWhenGeneratedEntitiesLackDocumentsDtoSetters(): void
    {
        // Every node-type resolves to the base class, which has no generated setter.
        $classLocator = $this->createMock(NodeTypeClassLocatorInterface::class);
        $classLocator->method('getSourceEntityFullQualifiedClassName')->willReturn(NodesSources::class);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('warning')
            ->with($this->stringContains('bin/console generate:nsentities'));

        $this->createWarmer($classLocator, $logger)->warmUp(sys_get_temp_dir());
    }

    public function testStaysSilentWhenGeneratedEntitiesAreUpToDate(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $this->createWarmer(static::getContainer()->get(NodeTypeClassLocatorInterface::class), $logger)
            ->warmUp(sys_get_temp_dir());
    }

    public function testStaysSilentWithoutDocumentDto(): void
    {
        $classLocator = $this->createMock(NodeTypeClassLocatorInterface::class);
        $classLocator->method('getSourceEntityFullQualifiedClassName')->willReturn(NodesSources::class);
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('warning');

        $this->createWarmer($classLocator, $logger, false)->warmUp(sys_get_temp_dir());
    }

    private function createWarmer(
        NodeTypeClassLocatorInterface $classLocator,
        LoggerInterface $logger,
        bool $useDocumentDto = true,
    ): DocumentsDtoSettersCacheWarmer {
        return new DocumentsDtoSettersCacheWarmer(
            static::getContainer()->get(NodeTypes::class),
            $classLocator,
            $logger,
            ['use_document_dto' => $useDocumentDto],
        );
    }
}
