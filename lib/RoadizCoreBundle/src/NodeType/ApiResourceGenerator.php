<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\Inflector\InflectorFactory;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Api\Controller\GetWebResponseByPathController;
use RZ\Roadiz\CoreBundle\Api\Model\WebResponseInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Yaml;

final readonly class ApiResourceGenerator
{
    /**
     * @param class-string<WebResponseInterface> $webResponseClass
     */
    public function __construct(
        private ApiResourceOperationNameGenerator $apiResourceOperationNameGenerator,
        private string $apiResourcesDir,
        private LoggerInterface $logger,
        private string $webResponseClass,
    ) {
    }

    /**
     * @return string|null generated resource file path or null if nothing done
     */
    public function generate(NodeTypeInterface $nodeType): ?string
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->apiResourcesDir)) {
            throw new \LogicException($this->apiResourcesDir.' folder does not exist.');
        }

        $resourcePath = $this->getResourcePath($nodeType);
        $webResponseResourcePath = $this->getWebResponseResourcePath();

        if (!$filesystem->exists($webResponseResourcePath)) {
            $filesystem->dumpFile(
                $webResponseResourcePath,
                Yaml::dump([
                    'resources' => [
                        $this->webResponseClass => [
                            'operations' => [],
                        ],
                    ],
                ], 7)
            );
        }
        $filesystem->dumpFile(
            $webResponseResourcePath,
            Yaml::dump($this->addWebResponseResourceOperation($nodeType, $webResponseResourcePath), 7)
        );
        $this->logger->info('API WebResponse config file has been updated.', [
            'file' => $webResponseResourcePath,
        ]);
        \clearstatcache(true, $webResponseResourcePath);

        if (!$filesystem->exists($resourcePath)) {
            $filesystem->dumpFile(
                $resourcePath,
                Yaml::dump($this->getApiResourceDefinition($nodeType), 7)
            );
            $this->logger->info('API resource config file has been generated.', [
                'nodeType' => $nodeType->getName(),
                'file' => $resourcePath,
            ]);
            \clearstatcache(true, $resourcePath);

            return $resourcePath;
        }

        return null;
    }

    public function remove(NodeTypeInterface $nodeType): void
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->apiResourcesDir)) {
            throw new \LogicException($this->apiResourcesDir.' folder does not exist.');
        }

        $resourcePath = $this->getResourcePath($nodeType);
        $webResponseResourcePath = $this->getWebResponseResourcePath();

        if ($filesystem->exists($webResponseResourcePath)) {
            $filesystem->dumpFile(
                $webResponseResourcePath,
                Yaml::dump($this->removeWebResponseResourceOperation($nodeType, $webResponseResourcePath), 7)
            );
            $this->logger->info('API WebResponse config file has been updated.', [
                'file' => $webResponseResourcePath,
            ]);
            \clearstatcache(true, $webResponseResourcePath);
        }

        if ($filesystem->exists($resourcePath)) {
            $filesystem->remove($resourcePath);
            $this->logger->info('API resource config file has been removed.', [
                'nodeType' => $nodeType->getName(),
                'file' => $resourcePath,
            ]);
            @\clearstatcache(true, $resourcePath);
        }
    }

    public function getResourcePath(NodeTypeInterface $nodeType): string
    {
        return $this->apiResourcesDir.'/'.(new UnicodeString($nodeType->getName()))
                ->lower()
                ->prepend('ns')
                ->append('.yml')
                ->toString();
    }

    private function getWebResponseResourcePath(): string
    {
        return $this->apiResourcesDir.'/web_response.yml';
    }

    private function getResourceName(string $nodeTypeName): string
    {
        return (new UnicodeString($nodeTypeName))
                ->snake()
                ->lower()
                ->toString();
    }

    private function getResourceUriPrefix(NodeTypeInterface $nodeType): string
    {
        $pluralNodeTypeName = InflectorFactory::create()->build()->pluralize($nodeType->getName());

        return '/'.$this->getResourceName($pluralNodeTypeName);
    }

    private function getApiResourceDefinition(NodeTypeInterface $nodeType): array
    {
        $fqcn = (new UnicodeString($nodeType->getSourceEntityFullQualifiedClassName()))
            ->trimStart('\\')
            ->toString();

        return [
            'resources' => [
                $fqcn => [
                    'shortName' => $nodeType->getName(),
                    'types' => [$nodeType->getName()],
                    'operations' => [
                        ...$this->getCollectionOperations($nodeType),
                        ...$this->getItemOperations($nodeType),
                    ],
                ],
            ],
        ];
    }

    private function addWebResponseResourceOperation(NodeTypeInterface $nodeType, string $webResponseResourcePath): array
    {
        $getByPathOperationName = $this->apiResourceOperationNameGenerator->generateGetByPath(
            $nodeType->getSourceEntityFullQualifiedClassName()
        );
        $webResponseResource = Yaml::parseFile($webResponseResourcePath);

        if (!\array_key_exists($this->webResponseClass, $webResponseResource['resources'])) {
            $webResponseResource = [
                'resources' => [
                    $this->webResponseClass => [
                        'operations' => [],
                    ],
                ],
            ];
        }

        if (\array_key_exists('operations', $webResponseResource['resources'][$this->webResponseClass])) {
            $operations = $webResponseResource['resources'][$this->webResponseClass]['operations'];
        } else {
            $operations = [];
        }

        if (!$nodeType->isReachable()) {
            // Do not add operation if node-type is not reachable
            return $webResponseResource;
        }
        if (\array_key_exists($getByPathOperationName, $operations)) {
            // Do not add operation if already exists
            return $webResponseResource;
        }

        $groups = $this->getItemOperationSerializationGroups($nodeType);
        $operations[$getByPathOperationName] = [
            'method' => 'GET',
            'class' => Get::class,
            'uriTemplate' => '/web_response_by_path',
            'read' => false,
            'controller' => GetWebResponseByPathController::class,
            'normalizationContext' => [
                'pagination_enabled' => false,
                'enable_max_depth' => true,
                'groups' => [
                    $getByPathOperationName,
                    ...array_values(array_filter(array_unique($groups))),
                    ...[
                        'web_response',
                        'walker',
                        'children',
                    ],
                ],
            ],
            'openapiContext' => [
                'tags' => ['WebResponse'],
                'summary' => 'Get a '.$nodeType->getName().' by its path wrapped in a WebResponse object',
                'description' => 'Get a '.$nodeType->getName().' by its path wrapped in a WebResponse',
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
                    ],
                ],
            ],
        ];

        $webResponseResource['resources'][$this->webResponseClass]['operations'] = $operations;

        return $webResponseResource;
    }

    private function removeWebResponseResourceOperation(NodeTypeInterface $nodeType, string $webResponseResourcePath): array
    {
        $getByPathOperationName = $this->apiResourceOperationNameGenerator->generateGetByPath(
            $nodeType->getSourceEntityFullQualifiedClassName()
        );
        $webResponseResource = Yaml::parseFile($webResponseResourcePath);

        if (!\array_key_exists($this->webResponseClass, $webResponseResource['resources'])) {
            return $webResponseResource;
        }
        if (\array_key_exists('operations', $webResponseResource['resources'][$this->webResponseClass])) {
            $operations = $webResponseResource['resources'][$this->webResponseClass]['operations'];
        } else {
            return $webResponseResource;
        }
        if (!\array_key_exists($getByPathOperationName, $operations)) {
            // Do not remove operation if it does not exist
            return $webResponseResource;
        }

        unset($operations[$getByPathOperationName]);
        $webResponseResource['resources'][$this->webResponseClass]['operations'] = array_filter($operations);

        return $webResponseResource;
    }

    private function getCollectionOperations(NodeTypeInterface $nodeType): array
    {
        if (!$nodeType->isReachable()) {
            return [];
        }
        $operations = [];
        $groups = [
            'nodes_sources_base',
            'nodes_sources_default',
            'urls',
            'tag_base',
            'translation_base',
            'document_display',
            'document_thumbnails',
            'document_display_sources',
            ...$this->getGroupedFieldsSerializationGroups($nodeType),
        ];

        $collectionOperationName = $this->apiResourceOperationNameGenerator->generate(
            $nodeType->getSourceEntityFullQualifiedClassName(),
            'get_collection'
        );
        $operations = array_merge(
            $operations,
            [
                $collectionOperationName => [
                    'method' => 'GET',
                    'class' => GetCollection::class,
                    'shortName' => $nodeType->getName(),
                    'normalizationContext' => [
                        'enable_max_depth' => true,
                        'groups' => array_values(array_filter(array_unique($groups))),
                    ],
                ],
            ]
        );
        if ($nodeType->isPublishable()) {
            $archivesOperationName = $this->apiResourceOperationNameGenerator->generate(
                $nodeType->getSourceEntityFullQualifiedClassName(),
                'archives_collection'
            );
            $operations = array_merge(
                $operations,
                [
                    $archivesOperationName => [
                        'method' => 'GET',
                        'class' => GetCollection::class,
                        'shortName' => $nodeType->getName(),
                        'uriTemplate' => $this->getResourceUriPrefix($nodeType).'/archives',
                        'extraProperties' => [
                            'archive_enabled' => true,
                        ],
                        'openapiContext' => [
                            'summary' => sprintf(
                                'Retrieve all %s ressources archives months and years',
                                $nodeType->getName()
                            ),
                        ],
                    ],
                ]
            );
        }

        return $operations;
    }

    private function getItemOperationSerializationGroups(NodeTypeInterface $nodeType): array
    {
        return [
            'nodes_sources',
            'node_listing',
            'urls',
            'tag_base',
            'translation_base',
            'document_display',
            'document_thumbnails',
            'document_display_sources',
            ...$this->getGroupedFieldsSerializationGroups($nodeType),
        ];
    }

    private function getItemOperations(NodeTypeInterface $nodeType): array
    {
        if (!$nodeType->isReachable()) {
            return [];
        }
        $groups = $this->getItemOperationSerializationGroups($nodeType);
        $itemOperationName = $this->apiResourceOperationNameGenerator->generate(
            $nodeType->getSourceEntityFullQualifiedClassName(),
            'get'
        );

        return [
            $itemOperationName => [
                'method' => 'GET',
                'class' => Get::class,
                'shortName' => $nodeType->getName(),
                'normalizationContext' => [
                    'groups' => array_values(array_filter(array_unique($groups))),
                ],
            ],
        ];
    }

    private function getGroupedFieldsSerializationGroups(NodeTypeInterface $nodeType): array
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
