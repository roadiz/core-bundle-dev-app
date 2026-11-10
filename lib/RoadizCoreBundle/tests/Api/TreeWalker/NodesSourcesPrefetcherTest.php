<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\TreeWalker;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodesSourcesPrefetcher;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Node;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\NodeType\NodeTypeClassLocator;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\CoreBundle\Tests\Api\TreeWalker\Fixtures\NSPrefetchBlock;
use RZ\Roadiz\CoreBundle\Tests\Api\TreeWalker\Fixtures\NSPrefetchPage;
use RZ\TreeWalker\WalkerInterface;

final class NodesSourcesPrefetcherTest extends TestCase
{
    private DocumentRepository&MockObject $documentRepository;
    private NodesSourcesRepository&MockObject $nodesSourcesRepository;
    private Translation $translation;

    #[\Override]
    protected function setUp(): void
    {
        $this->documentRepository = $this->createMock(DocumentRepository::class);
        $this->nodesSourcesRepository = $this->createMock(NodesSourcesRepository::class);
        $this->translation = $this->createTranslation(1);
    }

    public function testFillsDocumentsBuffersInPositionOrder(): void
    {
        $page = $this->createPage(10, 100);
        $first = new DocumentDto(2);
        $second = new DocumentDto(1);

        $this->documentRepository->expects($this->once())
            ->method('findDocumentDtoByNodeSourceIdsAndFieldNames')
            ->with([100], ['images'], $this->translation)
            ->willReturn([100 => ['images' => [$first, $second]]]);

        $this->createPrefetcher()->prefetch([$page]);

        $this->assertSame([$first, $second], $page->getImagesBuffer());
    }

    public function testFillsReferencesBuffersInPositionOrderAndReturnsThem(): void
    {
        $page = $this->createPage(10, 100);
        $otherPage = $this->createPage(11, 101);
        $first = $this->createBlock(20, 200);
        $second = $this->createBlock(21, 201);

        $this->nodesSourcesRepository->expects($this->once())
            ->method('findByNodeIdsAndFieldNamesAndTranslation')
            ->with([10, 11], ['related', 'any'], $this->translation)
            ->willReturn([
                10 => ['related' => [$first, $second], 'any' => [$second]],
                11 => ['related' => [$second]],
            ]);

        $loaded = $this->createPrefetcher()->prefetch([$page, $otherPage]);

        $this->assertSame([$first, $second], $page->getRelatedBuffer());
        // One node referenced twice appears in each list.
        $this->assertSame([$second], $page->getAnyBuffer());
        $this->assertSame([$second], $otherPage->getRelatedBuffer());
        $this->assertSame([$first, $second, $second, $second], $loaded);
    }

    public function testLeavesInitializedBuffersUntouched(): void
    {
        $page = $this->createPage(10, 100);
        $alreadyLoaded = [new DocumentDto(3)];
        $page->setImagesDtos($alreadyLoaded);
        $page->setRelatedSources([]);
        $page->setAnySources([]);

        $this->documentRepository->expects($this->never())->method('findDocumentDtoByNodeSourceIdsAndFieldNames');
        $this->nodesSourcesRepository->expects($this->never())->method('findByNodeIdsAndFieldNamesAndTranslation');

        $this->createPrefetcher()->prefetch([$page]);

        $this->assertSame($alreadyLoaded, $page->getImagesBuffer());
    }

    public function testSetsEmptyListsForFieldsWithoutResults(): void
    {
        $page = $this->createPage(10, 100);
        $this->documentRepository->method('findDocumentDtoByNodeSourceIdsAndFieldNames')->willReturn([]);
        $this->nodesSourcesRepository->method('findByNodeIdsAndFieldNamesAndTranslation')->willReturn([]);

        $this->createPrefetcher()->prefetch([$page]);

        $this->assertSame([], $page->getImagesBuffer());
        $this->assertSame([], $page->getRelatedBuffer());
        $this->assertSame([], $page->getAnyBuffer());
    }

    public function testFiltersReferencesByFieldAllowedNodeTypes(): void
    {
        $page = $this->createPage(10, 100);
        $block = $this->createBlock(20, 200);
        $referencedPage = $this->createPage(21, 201);

        $this->nodesSourcesRepository->method('findByNodeIdsAndFieldNamesAndTranslation')->willReturn([
            10 => [
                'related' => [$referencedPage, $block],
                'any' => [$referencedPage, $block],
            ],
        ]);

        $this->createPrefetcher()->prefetch([$page]);

        // "related" only allows PrefetchBlock, "any" has no default values.
        $this->assertSame([$block], $page->getRelatedBuffer());
        $this->assertSame([$referencedPage, $block], $page->getAnyBuffer());
    }

    public function testNeverMixesTranslationsInOneQuery(): void
    {
        $otherTranslation = $this->createTranslation(2);
        $page = $this->createPage(10, 100);
        $translatedPage = $this->createPage(10, 300, $otherTranslation);
        $dto = new DocumentDto(1);
        $translatedDto = new DocumentDto(2);

        $this->documentRepository->expects($this->exactly(2))
            ->method('findDocumentDtoByNodeSourceIdsAndFieldNames')
            ->withConsecutive(
                [[100], ['images'], $this->translation],
                [[300], ['images'], $otherTranslation],
            )
            ->willReturnOnConsecutiveCalls(
                [100 => ['images' => [$dto]]],
                [300 => ['images' => [$translatedDto]]],
            );

        $this->createPrefetcher()->prefetch([$page, $translatedPage]);

        $this->assertSame([$dto], $page->getImagesBuffer());
        $this->assertSame([$translatedDto], $translatedPage->getImagesBuffer());
    }

    public function testSkipsFieldsWithoutGeneratedSetter(): void
    {
        $page = $this->createPage(10, 100);
        $this->documentRepository->method('findDocumentDtoByNodeSourceIdsAndFieldNames')
            ->willReturn([100 => ['legacy' => [new DocumentDto(1)]]]);

        $this->createPrefetcher()->prefetch([$page]);

        // Left to its lazy getter.
        $this->assertNull($page->getLegacyBuffer());
    }

    public function testPrefetchWalkersPrefetchesNestedItemsExtraSourcesAndReferences(): void
    {
        $root = $this->createPage(10, 100);
        $child = $this->createPage(11, 101);
        $grandChild = $this->createPage(12, 102);
        $reference = $this->createPage(13, 103);
        $walker = $this->createWalker($child, [$this->createWalker($grandChild)]);

        $this->documentRepository->expects($this->exactly(2))
            ->method('findDocumentDtoByNodeSourceIdsAndFieldNames')
            ->withConsecutive(
                [[100, 101, 102], ['images'], $this->translation],
                [[103], ['images'], $this->translation],
            )
            ->willReturn([]);
        $this->nodesSourcesRepository->method('findByNodeIdsAndFieldNamesAndTranslation')
            ->willReturnOnConsecutiveCalls([12 => ['any' => [$reference]]], []);

        $this->createPrefetcher()->prefetchWalkers([$walker], [$root]);

        $this->assertSame([$reference], $grandChild->getAnyBuffer());
        $this->assertSame([], $reference->getImagesBuffer());
    }

    /**
     * @param array<WalkerInterface> $children
     */
    private function createWalker(object $item, array $children = []): WalkerInterface
    {
        $walker = $this->createMock(WalkerInterface::class);
        $walker->method('getItem')->willReturn($item);
        $walker->method('getChildren')->willReturn(new ArrayCollection($children));

        return $walker;
    }

    private function createPrefetcher(): NodesSourcesPrefetcher
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getRepository')->willReturnCallback(fn (string $class) => match ($class) {
            Document::class => $this->documentRepository,
            NodesSources::class => $this->nodesSourcesRepository,
        });

        $nodeTypes = [
            'PrefetchPage' => (new NodeType())
                ->setName('PrefetchPage')
                ->addField($this->createField('images', FieldType::DOCUMENTS_T))
                ->addField($this->createField('legacy', FieldType::DOCUMENTS_T))
                ->addField($this->createField('related', FieldType::NODES_T, '[PrefetchBlock]'))
                ->addField($this->createField('any', FieldType::NODES_T)),
            'PrefetchBlock' => (new NodeType())->setName('PrefetchBlock'),
        ];
        $nodeTypeResolver = new class($nodeTypes) implements NodeTypeResolverInterface {
            /**
             * @param array<string, NodeType> $nodeTypes
             */
            public function __construct(private readonly array $nodeTypes)
            {
            }

            #[\Override]
            public function get(string $nodeTypeName): ?NodeType
            {
                return $this->nodeTypes[$nodeTypeName] ?? null;
            }
        };

        return new NodesSourcesPrefetcher(
            $managerRegistry,
            $nodeTypeResolver,
            new NodeTypeClassLocator(__NAMESPACE__.'\\Fixtures', __NAMESPACE__.'\\Fixtures'),
        );
    }

    private function createField(string $name, FieldType $type, ?string $defaultValues = null): NodeTypeField
    {
        return (new NodeTypeField())
            ->setName($name)
            ->setType($type)
            ->setDefaultValues($defaultValues);
    }

    private function createTranslation(int $id): Translation
    {
        $translation = new Translation();
        $translation->setId($id);

        return $translation;
    }

    private function createNode(int $nodeId): Node
    {
        $node = new Node();
        $node->setId($nodeId);

        return $node;
    }

    private function createPage(int $nodeId, int $sourceId, ?Translation $translation = null): NSPrefetchPage
    {
        $page = new NSPrefetchPage($this->createNode($nodeId), $translation ?? $this->translation);
        $page->setId($sourceId);

        return $page;
    }

    private function createBlock(int $nodeId, int $sourceId): NSPrefetchBlock
    {
        $block = new NSPrefetchBlock($this->createNode($nodeId), $this->translation);
        $block->setId($sourceId);

        return $block;
    }
}
