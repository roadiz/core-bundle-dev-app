<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use Doctrine\Inflector\InflectorFactory;
use LogicException;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

final class ApiResourceGenerator
{
    private string $apiResourcesDir;
    private LoggerInterface $logger;

    public function __construct(string $apiResourcesDir, LoggerInterface $logger)
    {
        $this->apiResourcesDir = $apiResourcesDir;
        $this->logger = $logger;
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
            $this->logger->info('API resource config file has been generated.', [
                'nodeType' => $nodeType->getName(),
                'file' => $resourcePath,
            ]);
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
            $this->logger->info('API resource config file has been removed.', [
                'nodeType' => $nodeType->getName(),
                'file' => $resourcePath,
            ]);
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

    protected function getResourceName(string $nodeTypeName): string
    {
        return (new UnicodeString($nodeTypeName))
                ->snake()
                ->lower()
                ->toString();
    }

    protected function getResourceUriPrefix(NodeTypeInterface $nodeType): string
    {
        $pluralNodeTypeName = InflectorFactory::create()->build()->pluralize($nodeType->getName());
        return '/' . $this->getResourceName($pluralNodeTypeName);
    }

    protected function getApiResourceDefinition(NodeTypeInterface $nodeType): array
    {
        $fqcn = (new UnicodeString($nodeType->getSourceEntityFullQualifiedClassName()))
            ->trimStart('\\')
            ->toString();

        return [ $fqcn => [
            'types' => [$nodeType->getName()],
            'operations' => [
                ...$this->getCollectionOperations($nodeType),
                ...$this->getItemOperations($nodeType)
            ],
        ]];
    }

    protected function getCollectionOperations(NodeTypeInterface $nodeType): array
    {
        $operations = [];
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
            $operations = array_merge(
                $operations,
                [
                    'ApiPlatform\Metadata\GetCollection' => [
                        'method' => 'GET',
                        'shortName' => $nodeType->getName(),
                        'normalizationContext' => [
                            'enable_max_depth' => true,
                            'groups' => array_values(array_filter(array_unique($groups)))
                        ],
                    ]
                ]
            );
        }
        if ($nodeType->isPublishable()) {
            $archivesRouteName = '_api_' . $this->getResourceName($nodeType->getName()) . '_archives';
            $operations = array_merge(
                $operations,
                [
                    $archivesRouteName => [
                        'method' => 'GET',
                        'class' => 'ApiPlatform\Metadata\GetCollection',
                        'shortName' => $nodeType->getName(),
                        'uriTemplate' => $this->getResourceUriPrefix($nodeType) . '/archives',
                        'extraProperties' => [
                            'archive_enabled' => true,
                        ],
                        'openapiContext' => [
                            'summary' => sprintf(
                                'Retrieve all %s ressources archives months and years',
                                $nodeType->getName()
                            ),
                        ],
                    ]
                ]
            );
        }
        return $operations;
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
            'ApiPlatform\Metadata\Get' => [
                'method' => 'GET',
                'shortName' => $nodeType->getName(),
                'normalizationContext' => [
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
                'class' => 'ApiPlatform\Metadata\Get',
                'uriTemplate' => '/web_response_by_path',
                'read' => false,
                'controller' => 'RZ\Roadiz\CoreBundle\Api\Controller\GetWebResponseByPathController',
                'normalizationContext' => [
                    'pagination_enabled' => false,
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
                'openapiContext' => [
                    'tags' => ['WebResponse'],
                    'summary' => 'Get a resource by its path wrapped in a WebResponse object',
                    'description' => 'Get a resource by its path wrapped in a WebResponse',
                    'parameters' => [
                        [
                            'type' => 'string',
                            'name' => 'path',
                            'in' => 'query',
                            'required' => true,
                            'description' => 'Resource path, or `/` for home page',
                            'schema' => [
                                'type' => 'string',
                            ],
                        ]
                    ]
                ]
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
