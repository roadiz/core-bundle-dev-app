<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeResolverInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;
use RZ\Roadiz\EntityGenerator\Field\DocumentsFieldGenerator;
use RZ\Roadiz\EntityGenerator\Field\NodesFieldGenerator;
use RZ\TreeWalker\WalkerInterface;

/**
 * Fills generated node-sources documents and references buffers for many node-sources at once,
 * so that their getters do not issue one query per node-source and per field during serialization.
 */
final readonly class NodesSourcesPrefetcher
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private NodeTypeResolverInterface $nodeTypeResolver,
        private NodeTypeClassLocatorInterface $nodeTypeClassLocator,
    ) {
    }

    /**
     * Prefetches every walker item down to its max level, plus $sources, then the sources they reference.
     *
     * @param iterable<WalkerInterface> $walkers
     * @param array<NodesSources>       $sources
     */
    public function prefetchWalkers(iterable $walkers, array $sources = []): void
    {
        foreach ($walkers as $walker) {
            $this->collectWalkerItems($walker, $sources);
        }

        // Referenced sources media and URLs are often normalized too (menu links, cross links).
        $this->prefetch($this->prefetch($sources));
    }

    /**
     * Fills documents and references buffers that are still uninitialized.
     *
     * @param array<NodesSources> $sources
     *
     * @return array<NodesSources> referenced node-sources loaded by this prefetch
     */
    public function prefetch(array $sources): array
    {
        $sourcesByTranslation = [];
        foreach ($sources as $source) {
            if (null === $source->getId()) {
                continue;
            }
            $sourcesByTranslation[spl_object_id($source->getTranslation())][$source->getId()] = $source;
        }

        $references = [];
        foreach ($sourcesByTranslation as $translatedSources) {
            $translation = reset($translatedSources)->getTranslation();
            $this->prefetchDocuments($translatedSources, $translation);
            $references = [...$references, ...$this->prefetchReferences($translatedSources, $translation)];
        }

        return $references;
    }

    /**
     * @param array<int|string, NodesSources> $sources indexed by ID
     */
    private function prefetchDocuments(array $sources, TranslationInterface $translation): void
    {
        /** @var array<int|string, array<string, string>> $setters setter names indexed by node-source ID and field name */
        $setters = [];
        foreach ($sources as $id => $source) {
            foreach ($this->getFields($source) as $field) {
                if (!$field->isDocuments()) {
                    continue;
                }
                $setter = DocumentsFieldGenerator::getDtoSetterName($field);
                // Entities generated before DTO setters existed are not prefetched.
                if (method_exists($source, $setter) && $this->isBufferUninitialized($source, $field->getVarName())) {
                    $setters[$id][$field->getName()] = $setter;
                }
            }
        }
        if ([] === $setters) {
            return;
        }

        /** @var DocumentRepository $repository */
        $repository = $this->managerRegistry->getRepository(Document::class);
        $documents = $repository->findDocumentDtoByNodeSourceIdsAndFieldNames(
            array_keys($setters),
            $this->getFieldNames($setters),
            $translation
        );

        foreach ($setters as $id => $fieldSetters) {
            foreach ($fieldSetters as $fieldName => $setter) {
                $sources[$id]->$setter($documents[$id][$fieldName] ?? []);
            }
        }
    }

    /**
     * @param array<int|string, NodesSources> $sources indexed by ID
     *
     * @return array<NodesSources>
     */
    private function prefetchReferences(array $sources, TranslationInterface $translation): array
    {
        /** @var array<int|string, array<string, NodeTypeField>> $fields indexed by node-source ID and field name */
        $fields = [];
        foreach ($sources as $id => $source) {
            foreach ($this->getFields($source) as $field) {
                if (
                    $field->isNodes()
                    && method_exists($source, NodesFieldGenerator::getSourcesSetterName($field))
                    && $this->isBufferUninitialized($source, NodesFieldGenerator::getSourcesPropertyName($field))
                ) {
                    $fields[$id][$field->getName()] = $field;
                }
            }
        }
        if ([] === $fields) {
            return [];
        }

        /** @var NodesSourcesRepository<NodesSources> $repository */
        $repository = $this->managerRegistry->getRepository(NodesSources::class);
        $nodeIds = array_values(array_filter(array_map(
            fn (int|string $id) => $sources[$id]->getNode()->getId(),
            array_keys($fields)
        )));
        $references = $repository->findByNodeIdsAndFieldNamesAndTranslation(
            $nodeIds,
            $this->getFieldNames($fields),
            $translation
        );

        $loaded = [];
        foreach ($fields as $id => $sourceFields) {
            $source = $sources[$id];
            foreach ($sourceFields as $fieldName => $field) {
                $fieldReferences = $this->filterAllowedNodeTypes(
                    $references[$source->getNode()->getId() ?? 0][$fieldName] ?? [],
                    $field
                );
                $source->{NodesFieldGenerator::getSourcesSetterName($field)}($fieldReferences);
                $loaded = [...$loaded, ...$fieldReferences];
            }
        }

        return $loaded;
    }

    /**
     * Same filter as generated getters, which query allowed node-types with INSTANCE OF.
     *
     * @param array<NodesSources> $references
     *
     * @return array<NodesSources>
     */
    private function filterAllowedNodeTypes(array $references, NodeTypeField $field): array
    {
        $allowedClasses = [];
        foreach ($field->getDefaultValuesAsArray() as $nodeTypeName) {
            $nodeType = $this->nodeTypeResolver->get(trim((string) $nodeTypeName));
            if (null !== $nodeType) {
                $allowedClasses[] = $this->nodeTypeClassLocator->getSourceEntityFullQualifiedClassName($nodeType);
            }
        }
        if ([] === $allowedClasses) {
            return $references;
        }

        return array_values(array_filter(
            $references,
            function (NodesSources $reference) use ($allowedClasses) {
                foreach ($allowedClasses as $allowedClass) {
                    if ($reference instanceof $allowedClass) {
                        return true;
                    }
                }

                return false;
            }
        ));
    }

    /**
     * @return iterable<NodeTypeField>
     */
    private function getFields(NodesSources $source): iterable
    {
        $nodeType = $this->nodeTypeResolver->get($source->getNodeTypeName());
        if (!$nodeType instanceof NodeType) {
            return [];
        }

        return $nodeType->getFields();
    }

    /**
     * Generated buffers are private: a null buffer means its getter has not been called yet.
     */
    private function isBufferUninitialized(NodesSources $source, string $property): bool
    {
        // Walk up from Doctrine proxies to the generated class declaring the property.
        $class = new \ReflectionClass($source);
        while (!$class->hasProperty($property)) {
            $class = $class->getParentClass();
            if (false === $class) {
                return false;
            }
        }

        return null === $class->getProperty($property)->getValue($source);
    }

    /**
     * @param array<array<string, mixed>> $fieldsBySource
     *
     * @return array<string>
     */
    private function getFieldNames(array $fieldsBySource): array
    {
        return array_values(array_unique(array_merge(...array_map(array_keys(...), array_values($fieldsBySource)))));
    }

    /**
     * @param array<NodesSources> $sources
     */
    private function collectWalkerItems(WalkerInterface $walker, array &$sources): void
    {
        $item = $walker->getItem();
        if ($item instanceof NodesSources) {
            $sources[] = $item;
        }
        // Children are memoized by the walker: serialization will not query them again.
        foreach ($walker->getChildren() as $child) {
            $this->collectWalkerItems($child, $sources);
        }
    }
}
