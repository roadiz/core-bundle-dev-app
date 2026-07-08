<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\EntityGenerator\EntityGenerator;

final class EntityGeneratorMetaDescriptionTest extends TestCase
{
    use NodeTypeAwareTestTrait;

    private const array OPTIONS = [
        'parent_class' => '\mock\Entity\NodesSources',
        'node_class' => '\mock\Entity\Node',
        'translation_class' => '\mock\Entity\Translation',
        'document_class' => '\mock\Entity\Document',
        'document_proxy_class' => '\mock\Entity\NodesSourcesDocument',
        'custom_form_class' => '\mock\Entity\CustomForm',
        'custom_form_proxy_class' => '\mock\Entity\NodesSourcesCustomForm',
        'repository_class' => '\mock\Entity\Repository\NodesSourcesRepository',
        'use_native_json' => true,
        'use_api_platform_filters' => true,
    ];

    /**
     * @param iterable<\RZ\Roadiz\Contracts\NodeType\NodeTypeFieldInterface> $fields
     */
    private function createNodeType(iterable $fields): NodeTypeInterface
    {
        $nodeType = $this->createStub(NodeTypeInterface::class);
        $nodeType->method('getFields')->willReturn(new ArrayCollection([...$fields]));
        $nodeType->method('getSourceEntityTableName')->willReturn('ns_mock');
        $nodeType->method('getSourceEntityClassName')->willReturn('NSMock');
        $nodeType->method('getName')->willReturn('Mock');
        $nodeType->method('isReachable')->willReturn(true);
        $nodeType->method('isPublishable')->willReturn(true);

        return $nodeType;
    }

    private function generate(NodeTypeInterface $nodeType): string
    {
        return (new EntityGenerator(
            $nodeType,
            $this->getMockNodeTypeResolver(),
            $this->getMockDefaultValuesResolver(),
            new SimpleNodeTypeClassLocator(
                'RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources',
                'RZ\Roadiz\EntityGenerator\Tests\Mocks\GeneratedNodesSources\Repository',
            ),
            self::OPTIONS,
        ))->getClassContent();
    }

    public function testFlaggedFieldGeneratesMetaDescriptionOverride(): void
    {
        $content = $this->generate($this->createNodeType([
            (new SimpleNodeTypeField())
                ->setName('excerpt')
                ->setTypeName('markdown')
                ->setDoctrineType('text')
                ->setVirtual(false)
                ->setLabel('Excerpt')
                ->setMetaDescriptionFallback(true),
        ]));

        $this->assertStringContainsString('public function getMetaDescriptionOrFallback(): string', $content);
        $this->assertStringContainsString('$metaDescription = $this->getMetaDescription();', $content);
        $this->assertStringContainsString('return (string) ($this->getExcerpt() ?? \'\');', $content);
        // The raw getter must NOT be overridden (would pollute the SEO form round-trip).
        $this->assertStringNotContainsString('public function getMetaDescription(): string', $content);
    }

    public function testUnflaggedFieldsGenerateNoOverride(): void
    {
        $content = $this->generate($this->createNodeType([
            (new SimpleNodeTypeField())
                ->setName('excerpt')
                ->setTypeName('markdown')
                ->setDoctrineType('text')
                ->setVirtual(false)
                ->setLabel('Excerpt'),
        ]));

        $this->assertStringNotContainsString('public function getMetaDescriptionOrFallback(): string', $content);
    }
}
