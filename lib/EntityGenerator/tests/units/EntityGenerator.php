<?php
declare(strict_types=1);

namespace tests\units\RZ\Roadiz\EntityGenerator;

use atoum;
use tests\mocks\NodeTypeAwareTrait;

class EntityGenerator extends atoum
{
    use NodeTypeAwareTrait;

    public function testGetClassContent()
    {
        $mockNodeType = $this->getMockNodeType();
        $mockNodeTypeResolver = $this->getMockNodeTypeResolver();

        /*
         * Uncomment for generating a mock file from tests
         */
//        $dumpInstance = $this->newTestedInstance($mockNodeType, $mockNodeTypeResolver, [
//            'parent_class' => '\mock\Entity\NodesSources',
//            'node_class' => '\mock\Entity\Node',
//            'translation_class' => '\mock\Entity\Translation',
//            'document_class' => '\mock\Entity\Document',
//            'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
//            'custom_form_class' => '\mock\Entity\CustomForm',
//            'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
//            'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
//            'namespace' => '\tests\mocks\GeneratedNodesSources',
//            'use_native_json' => true,
//            'use_api_platform_filters' => true,
//        ]);
//        file_put_contents(
//            dirname(__DIR__) . '/mocks/GeneratedNodesSources/NSMock.php',
//            $dumpInstance->getClassContent()
//        );

        $this
            // creation of a new instance of the tested class
            ->given($this->newTestedInstance($mockNodeType, $mockNodeTypeResolver, [
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
            ->string($this->testedInstance->getClassContent())
            ->isEqualTo(file_get_contents(dirname(__DIR__) . '/mocks/GeneratedNodesSources/NSMock.php'))
        ;

        /**
         * TEST without leading slashs
         */
        $this
            // creation of a new instance of the tested class
            ->given($this->newTestedInstance($mockNodeType, $mockNodeTypeResolver, [
                'parent_class' => 'mock\Entity\NodesSources',
                'node_class' => 'mock\Entity\Node',
                'translation_class' => 'mock\Entity\Translation',
                'document_class' => 'mock\Entity\Document',
                'document_proxy_class' => 'mock\Entity\NodesSourcesDocument',
                'custom_form_class' => 'mock\Entity\CustomForm',
                'custom_form_proxy_class' => 'mock\Entity\NodesSourcesCustomForm',
                'repository_class' => 'mock\Entity\Repository\NodesSourcesRepository',
                'namespace' => 'tests\mocks\GeneratedNodesSources',
                'use_native_json' => true,
                'use_api_platform_filters' => true,
            ]))
            ->then
            ->string($this->testedInstance->getClassContent())
            ->isEqualTo(file_get_contents(dirname(__DIR__) . '/mocks/GeneratedNodesSources/NSMock.php'))
        ;
    }
}
