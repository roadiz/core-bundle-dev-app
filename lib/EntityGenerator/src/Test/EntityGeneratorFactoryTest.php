<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Test;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\EntityGenerator\EntityGeneratorFactory;

class EntityGeneratorFactoryTest extends TestCase
{
    use NodeTypeAwareTestTrait;

    protected function getEntityGeneratorFactory(?array $options = null): EntityGeneratorFactory
    {
        return new EntityGeneratorFactory(
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
                'namespace' => '\tests\mocks\GeneratedNodesSources',
                'use_native_json' => true,
                'use_api_platform_filters' => true,
            ]
        );
    }

    public function testCreate(): void
    {
        $generator = $this->getEntityGeneratorFactory();

        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/../tests/mocks/GeneratedNodesSources/NSMock.php'),
            $generator->create($this->getMockNodeType())->getClassContent()
        );
    }

    public function testCreateWithCustomRepository(): void
    {
        $generator = $this->getEntityGeneratorFactory([
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
        ]);

        /*
         * Uncomment for generating a mock file from tests
         */
//        file_put_contents(
//            dirname(__DIR__) . '/../test/mocks/GeneratedNodesSourcesWithRepository/NSMock.php',
//            $generator->createWithCustomRepository($this->getMockNodeType())->getClassContent()
//        );

        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/../tests/mocks/GeneratedNodesSourcesWithRepository/NSMock.php'),
            $generator->createWithCustomRepository($this->getMockNodeType())->getClassContent()
        );
    }

    public function testCreateCustomRepository(): void
    {
        $generator = $this->getEntityGeneratorFactory([
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
        ]);

        /*
         * Uncomment for generating a mock file from tests
         */
//        file_put_contents(
//            dirname(__DIR__) . '/../test/mocks/GeneratedNodesSourcesWithRepository/NSMockRepository.php',
//            $generator->createCustomRepository($this->getMockNodeType())->getClassContent()
//        );

        $this->assertEquals(
            file_get_contents(dirname(__DIR__) . '/../tests/mocks/GeneratedNodesSourcesWithRepository/NSMockRepository.php'),
            $generator->createCustomRepository($this->getMockNodeType())->getClassContent()
        );
    }
}
