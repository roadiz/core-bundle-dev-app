<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\UnicodeString;

final readonly class NodesTypesFilesExporter
{
    public function __construct(
        private string $nodeTypesDir,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @return string|null generated resource file path or null if nothing done
     */
    public function generate(NodeTypeInterface $nodeType): ?string
    {
        $filesystem = new Filesystem();

        if (!$filesystem->exists($this->nodeTypesDir)) {
            throw new \LogicException($this->nodeTypesDir.' folder does not exist.');
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
                    'groups' => ['node_type:export'],
                    AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                ]
            )
        );
        \clearstatcache(true, $nodeTypePath);

        return $nodeTypePath;
    }

    public function getResourcePath(NodeTypeInterface $nodeType): string
    {
        return $this->nodeTypesDir.'/'.(new UnicodeString($nodeType->getName()))
                ->lower()
                ->append('.yaml')
                ->toString();
    }
}
