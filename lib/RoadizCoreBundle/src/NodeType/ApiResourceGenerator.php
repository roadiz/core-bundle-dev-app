<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use LogicException;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

final class ApiResourceGenerator
{
    private string $apiResourcesDir;

    public function __construct(string $apiResourcesDir)
    {
        $this->apiResourcesDir = $apiResourcesDir;
    }

    /**
     * @param NodeTypeInterface $nodeType
     * @return string|null Generated resource file path or null if nothing done.
     */
    public function generate(NodeTypeInterface $nodeType): ?string
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->apiResourcesDir)) {
            throw new LogicException($this->apiResourcesDir . ' folder does not exist.');
        }

        $resourcePath = $this->getResourcePath($nodeType);

        if (!$filesystem->exists($resourcePath)) {
            $filesystem->dumpFile(
                $resourcePath,
                Yaml::dump($this->getApiResourceDefinition($nodeType), 6)
            );
            \clearstatcache(true, $resourcePath);
            return $resourcePath;
        } else {
            return null;
        }
    }

    public function remove(NodeTypeInterface $nodeType): void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->apiResourcesDir)) {
            throw new LogicException($this->apiResourcesDir . ' folder does not exist.');
        }

        $resourcePath = $this->getResourcePath($nodeType);

        if ($filesystem->exists($resourcePath)) {
            $filesystem->remove($resourcePath);
            @\clearstatcache(true, $resourcePath);
        }
    }

    protected function getResourcePath(NodeTypeInterface $nodeType): string
    {
        return $this->apiResourcesDir . '/' . (new UnicodeString($nodeType->getName()))
                ->lower()
                ->prepend('ns')
                ->append('.yml')
                ->toString();
    }

    protected function getApiResourceDefinition(NodeTypeInterface $nodeType): array
    {
        $fqcn = (new UnicodeString($nodeType->getSourceEntityFullQualifiedClassName()))
            ->trimStart('\\')
            ->toString();

        return [ $fqcn => [
            'iri' => $nodeType->getName(),
            'shortName' => $nodeType->getName(),
            'collectionOperations' => $this->getCollectionOperations($nodeType),
            'itemOperations' => $this->getItemOperations($nodeType),
        ]];
    }

    protected function getCollectionOperations(NodeTypeInterface $nodeType): array
    {
        if ($nodeType->isReachable()) {
            $groups = [
                "nodes_sources_base",
                "nodes_sources_default",
                "urls",
                "tag_base",
                "translation_base",
                "document_display",
                "document_thumbnails",
                "document_display_sources",
                ...$this->getGroupedFieldsSerializationGroups($nodeType)
            ];
            return [
                'get' => [
                    'method' => 'GET',
                    'normalization_context' => [
                        'enable_max_depth' => true,
                        'groups' => array_values(array_filter(array_unique($groups)))
                    ],
                ]
            ];
        }
        return [];
    }

    protected function getItemOperations(NodeTypeInterface $nodeType): array
    {
        $groups = [
            "nodes_sources",
            "urls",
            "tag_base",
            "translation_base",
            "document_display",
            "document_thumbnails",
            "document_display_sources",
            ...$this->getGroupedFieldsSerializationGroups($nodeType)
        ];
        $operations = [
            'get' => [
                'method' => 'GET',
                'normalization_context' => [
                    'groups' => array_values(array_filter(array_unique($groups)))
                ],
            ]
        ];

        /*
         * Create itemOperation for WebResponseController action
         */
        if ($nodeType->isReachable()) {
            $operations['getByPath'] = [
                'method' => 'GET',
                'normalization_context' => [
                    'enable_max_depth' => true,
                    'groups' => array_merge(array_values(array_filter(array_unique($groups))), [
                        'web_response',
                        'walker',
                        'walker_level',
                        'walker_metadata',
                        'meta',
                        'children',
                    ])
                ],
            ];
        }

        return $operations;
    }

    protected function getGroupedFieldsSerializationGroups(NodeTypeInterface $nodeType): array
    {
        $groups = [];
        foreach ($nodeType->getFields() as $field) {
            if (null !== $field->getGroupNameCanonical()) {
                $groups[] = (new UnicodeString($field->getGroupNameCanonical()))
                    ->lower()
                    ->snake()
                    ->prepend('nodes_sources_')
                    ->toString()
                ;
            }
        }
        return $groups;
    }
}
