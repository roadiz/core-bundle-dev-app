<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\UnicodeString;

final readonly class NodesTypesFilesExporter
{
    public function __construct(
        private string $nodesTypesDir,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @return string|null generated resource file path or null if nothing done
     */
    public function generate(NodeTypeInterface $nodeType): ?string
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->nodesTypesDir)) {
            throw new \LogicException($this->nodesTypesDir.' folder does not exist.');
        }

        $nodeTypePath = $this->getResourcePath($nodeType);

        $filesystem->dumpFile(
            $nodeTypePath,
            $this->serializer->serialize(
                $nodeType,
                'yaml',
                [
                    'yaml_inline' => 7,
                    'yaml_indentation' => true,
                    'groups' => ['node_type:export', 'position'],
                ]
            )
        );
        \clearstatcache(true, $nodeTypePath);

        return $nodeTypePath;
    }

    public function getResourcePath(NodeTypeInterface $nodeType): string
    {
        return $this->nodesTypesDir.'/'.(new UnicodeString($nodeType->getName()))
                ->lower()
                ->append('.yaml')
                ->toString();
    }
}
