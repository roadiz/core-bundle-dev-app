<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\EntityGenerator\EntityGenerator;
use Symfony\Component\Filesystem\Filesystem;

class EntityGeneratorTest extends TestCase
{
    use NodeTypeAwareTestTrait;

    protected function getEntityGenerator(NodeTypeInterface $nodeType, ?array $options = null): EntityGenerator
    {
        return new EntityGenerator(
            $nodeType,
            $this->getMockNodeTypeResolver(),
            $this->getMockDefaultValuesResolver(),
            $options ?? [
                'parent_class' => '\mock\Entity\NodesSources',
                'node_class' => '\mock\Entity\Node',
                'translation_class' => '\mock\Entity\Translation',
                'document_class' => '\mock\Entity\Document',
                'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
                'custom_form_class' => '\mock\Entity\CustomForm',
                'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
                'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
                'namespace' => '\RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources',
                'use_native_json' => true,
                'use_api_platform_filters' => true,
            ]
        );
    }

    public function test(): void
    {
        $mockNodeType = $this->getMockNodeType();
        $generator = $this->getEntityGenerator($mockNodeType);

        /*
         * Uncomment for generating a mock file from tests
         */
        //        (new Filesystem())->dumpFile(
        //            dirname(__DIR__) . '/tests/Mocks/GeneratedNodesSources/NSMock.php',
        //            $generator->getClassContent()
        //        );

        $this->assertEquals(
            (new Filesystem())->readFile(dirname(__DIR__).'/tests/Mocks/GeneratedNodesSources/NSMock.php'),
            $generator->getClassContent()
        );

        /**
         * TEST without leading slashs.
         */
        $generatorWithoutLeadingSlashes = $this->getEntityGenerator($mockNodeType, [
            'parent_class' => 'mock\Entity\NodesSources',
            'node_class' => 'mock\Entity\Node',
            'translation_class' => 'mock\Entity\Translation',
            'document_class' => 'mock\Entity\Document',
            'document_proxy_class' => 'mock\Entity\NodesSourcesDocument',
            'custom_form_class' => 'mock\Entity\CustomForm',
            'custom_form_proxy_class' => 'mock\Entity\NodesSourcesCustomForm',
            'repository_class' => 'mock\Entity\Repository\NodesSourcesRepository',
            'namespace' => 'RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources',
            'use_native_json' => true,
            'use_api_platform_filters' => true,
        ]);
        $this->assertEquals(
            (new Filesystem())->readFile(dirname(__DIR__).'/tests/Mocks/GeneratedNodesSources/NSMock.php'),
            $generatorWithoutLeadingSlashes->getClassContent()
        );
    }

    public function testWithDocumentDto(): void
    {
        $mockNodeType = $this->getMockDocumentNodeType();
        $generator = $this->getEntityGenerator($mockNodeType, [
            'parent_class' => '\mock\Entity\NodesSources',
            'node_class' => '\mock\Entity\Node',
            'translation_class' => '\mock\Entity\Translation',
            'document_class' => '\mock\Entity\Document',
            'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
            'custom_form_class' => '\mock\Entity\CustomForm',
            'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
            'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
            'namespace' => '\RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources',
            'use_native_json' => true,
            'use_api_platform_filters' => true,
            'use_document_dto' => true,
        ]);

        /*
         * Uncomment for generating a mock file from tests
         */
        //        (new Filesystem())->dumpFile(
        //            dirname(__DIR__).'/tests/Mocks/GeneratedNodesSourcesWithDocumentDto/NSMock.php',
        //            $generator->getClassContent()
        //        );

        $this->assertEquals(
            (new Filesystem())->readFile(dirname(__DIR__).'/tests/Mocks/GeneratedNodesSourcesWithDocumentDto/NSMock.php'),
            $generator->getClassContent()
        );

        /**
         * TEST without leading slashs.
         */
        $generatorWithoutLeadingSlashes = $this->getEntityGenerator($mockNodeType, [
            'parent_class' => 'mock\Entity\NodesSources',
            'node_class' => 'mock\Entity\Node',
            'translation_class' => 'mock\Entity\Translation',
            'document_class' => 'mock\Entity\Document',
            'document_proxy_class' => 'mock\Entity\NodesSourcesDocument',
            'custom_form_class' => 'mock\Entity\CustomForm',
            'custom_form_proxy_class' => 'mock\Entity\NodesSourcesCustomForm',
            'repository_class' => 'mock\Entity\Repository\NodesSourcesRepository',
            'namespace' => 'RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources',
            'use_native_json' => true,
            'use_api_platform_filters' => true,
            'use_document_dto' => true,
        ]);
        $this->assertEquals(
            (new Filesystem())->readFile(dirname(__DIR__).'/tests/Mocks/GeneratedNodesSourcesWithDocumentDto/NSMock.php'),
            $generatorWithoutLeadingSlashes->getClassContent()
        );
    }
}
