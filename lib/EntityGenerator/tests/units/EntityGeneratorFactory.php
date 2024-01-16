<?php
declare(strict_types=1);

namespace tests\units\RZ\Roadiz\EntityGenerator;

use atoum;
use tests\mocks\NodeTypeAwareTrait;

class EntityGeneratorFactory extends atoum
{
    use NodeTypeAwareTrait;

    public function testCreate()
    {
        $mockNodeType = $this->getMockNodeType();
        $mockNodeTypeResolver = $this->getMockNodeTypeResolver();
        $mockDefaultValuesResolver = $this->getMockDefaultValuesResolver();

        $this
            // creation of a new instance of the tested class
            ->given($this->newTestedInstance($mockNodeTypeResolver, $mockDefaultValuesResolver, [
                'parent_class' => '\mock\Entity\NodesSources',
                'node_class' => '\mock\Entity\Node',
                'translation_class' => '\mock\Entity\Translation',
                'document_class' => '\mock\Entity\Document',
                'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
                'custom_form_class' => '\mock\Entity\CustomForm',
                'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
                'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
                'namespace' => '\tests\mocks\GeneratedNodesSources',
                'use_native_json' => true,
                'use_api_platform_filters' => true,
            ]))
            ->then
            ->string($this->testedInstance->create($mockNodeType)->getClassContent())
            ->isEqualTo(file_get_contents(dirname(__DIR__) . '/mocks/GeneratedNodesSources/NSMock.php'))
        ;
    }

    public function testCreateWithCustomRepository()
    {
        $mockNodeType = $this->getMockNodeType();
        $mockNodeTypeResolver = $this->getMockNodeTypeResolver();
        $mockDefaultValuesResolver = $this->getMockDefaultValuesResolver();

        /*
         * Uncomment for generating a mock file from tests
         */
//        $dumpInstance = $this->newTestedInstance($mockNodeTypeResolver, $mockDefaultValuesResolver, [
//            'parent_class' => '\mock\Entity\NodesSources',
//            'node_class' => '\mock\Entity\Node',
//            'translation_class' => '\mock\Entity\Translation',
//            'document_class' => '\mock\Entity\Document',
//            'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
//            'custom_form_class' => '\mock\Entity\CustomForm',
//            'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
//            'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
//            'namespace' => '\tests\mocks\GeneratedNodesSourcesWithRepository',
//            'use_native_json' => true,
//            'use_api_platform_filters' => true,
//        ]);
//        file_put_contents(
//            dirname(__DIR__) . '/mocks/GeneratedNodesSourcesWithRepository/NSMock.php',
//            $dumpInstance->createWithCustomRepository($mockNodeType)->getClassContent()
//        );

        $this
            // creation of a new instance of the tested class
            ->given($this->newTestedInstance($mockNodeTypeResolver, $mockDefaultValuesResolver, [
                'parent_class' => '\mock\Entity\NodesSources',
                'node_class' => '\mock\Entity\Node',
                'translation_class' => '\mock\Entity\Translation',
                'document_class' => '\mock\Entity\Document',
                'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
                'custom_form_class' => '\mock\Entity\CustomForm',
                'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
                'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
                'namespace' => '\tests\mocks\GeneratedNodesSourcesWithRepository',
                'use_native_json' => true,
                'use_api_platform_filters' => true,
            ]))
            ->then
            ->string($this->testedInstance->createWithCustomRepository($mockNodeType)->getClassContent())
            ->isEqualTo(file_get_contents(dirname(__DIR__) . '/mocks/GeneratedNodesSourcesWithRepository/NSMock.php'))
        ;
    }

    public function testCreateCustomRepository()
    {
        $mockNodeType = $this->getMockNodeType();
        $mockNodeTypeResolver = $this->getMockNodeTypeResolver();
        $mockDefaultValuesResolver = $this->getMockDefaultValuesResolver();

        /*
         * Uncomment for generating a mock file from tests
         */
//        $dumpInstance = $this->newTestedInstance($mockNodeTypeResolver, $mockDefaultValuesResolver, [
//            'parent_class' => '\mock\Entity\NodesSources',
//            'node_class' => '\mock\Entity\Node',
//            'translation_class' => '\mock\Entity\Translation',
//            'document_class' => '\mock\Entity\Document',
//            'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
//            'custom_form_class' => '\mock\Entity\CustomForm',
//            'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
//            'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
//            'namespace' => '\tests\mocks\GeneratedNodesSourcesWithRepository',
//            'use_native_json' => true,
//            'use_api_platform_filters' => true,
//        ]);
//        file_put_contents(
//            dirname(__DIR__) . '/mocks/GeneratedNodesSourcesWithRepository/NSMockRepository.php',
//            $dumpInstance->createCustomRepository($mockNodeType)->getClassContent()
//        );

        $this
            // creation of a new instance of the tested class
            ->given($this->newTestedInstance($mockNodeTypeResolver, $mockDefaultValuesResolver, [
                'parent_class' => '\mock\Entity\NodesSources',
                'node_class' => '\mock\Entity\Node',
                'translation_class' => '\mock\Entity\Translation',
                'document_class' => '\mock\Entity\Document',
                'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
                'custom_form_class' => '\mock\Entity\CustomForm',
                'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
                'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
                'namespace' => '\tests\mocks\GeneratedNodesSourcesWithRepository',
                'use_native_json' => true,
                'use_api_platform_filters' => true,
            ]))
            ->then
            ->string($this->testedInstance->createCustomRepository($mockNodeType)->getClassContent())
            ->isEqualTo(file_get_contents(dirname(__DIR__) . '/mocks/GeneratedNodesSourcesWithRepository/NSMockRepository.php'))
        ;
    }
}
