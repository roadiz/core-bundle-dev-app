<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\EntityGenerator\EntityGenerator;

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

    public function testGetClassContent(): void
    {
        $mockNodeType = $this->getMockNodeType();
        $generator = $this->getEntityGenerator($mockNodeType);

        /*
         * Uncomment for generating a mock file from tests
         */
//        file_put_contents(
//            dirname(__DIR__) . '/tests/Mocks/GeneratedNodesSources/NSMock.php',
//            $generator->getClassContent()
//        );

        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/tests/Mocks/GeneratedNodesSources/NSMock.php'),
            $generator->getClassContent()
        );

        /**
         * TEST without leading slashs
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
            file_get_contents(dirname(__DIR__) . '/tests/Mocks/GeneratedNodesSources/NSMock.php'),
            $generatorWithoutLeadingSlashes->getClassContent()
        );
    }
}
