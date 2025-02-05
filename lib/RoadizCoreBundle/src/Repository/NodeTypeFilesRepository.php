<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use RZ\Roadiz\CoreBundle\NodeType\Configuration\NodeTypeConfiguration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

final readonly class NodeTypeFilesRepository implements NodeTypeRepositoryInterface
{
    public function __construct(
        private string $nodeTypesDir,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private Stopwatch $stopwatch,
    ) {
    }

    /**
     * @return NodeType[]
     *
     * @throws \Exception
     */
    public function findAll(): array
    {
        $this->stopwatch->start('NodeTypeFilesRepository::findAll');
        $finder = new Finder();
        $finder->files()->in($this->nodeTypesDir);
        if (!$finder->hasResults()) {
            throw new \Exception('No files exist in this folder : '.$this->nodeTypesDir);
        }
        $nodeTypes = [];

        foreach ($finder as $file) {
            try {
                $content = $this->checkFile($file);
                if (null === $content) {
                    continue;
                }
                $nodeTypes[] = $this->deserialize($content);
            } catch (InvalidConfigurationException $e) {
                $e->addHint('File: '.$file->getRealPath());
                throw $e;
            }
        }

        $this->stopwatch->stop('NodeTypeFilesRepository::findAll');

        return $nodeTypes;
    }

    /**
     * @throws \Exception
     */
    public function findOneByName(string $name): ?NodeType
    {
        $finder = new Finder();
        $finder->files()->in($this->nodeTypesDir);
        if (!$finder->hasResults()) {
            throw new \Exception('No files exist in this folder : '.$this->nodeTypesDir);
        }

        $finder->filter(function (\SplFileInfo $file) use ($name) {
            return $this->supportName($file->getBasename(), $name);
        });

        $iterator = $finder->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();

        try {
            $content = $this->checkFile($firstFile);
            if (null === $content) {
                return null;
            }

            return $this->deserialize($content);
        } catch (InvalidConfigurationException $e) {
            $e->addHint('File: '.$firstFile->getRealPath());
            throw $e;
        }
    }

    private function checkFile(?\SplFileInfo $file): ?string
    {
        if (null === $file) {
            return null;
        }
        $content = file_get_contents($file->getRealPath());
        if (false === $content) {
            return null;
        }
        if (empty($content)) {
            return null;
        }

        return $content;
    }

    private function supportName(string $fileName, string $name): bool
    {
        $supported = [
            ucfirst(mb_strtolower($name)),
            lcfirst(mb_strtolower($name)),
            $name.'.yml',
            $name.'.yaml',
            ucfirst(mb_strtolower($name)).'.yml',
            ucfirst(mb_strtolower($name)).'.yaml',
            lcfirst(mb_strtolower($name)).'.yml',
            lcfirst(mb_strtolower($name)).'.yaml',
        ];

        return in_array($fileName, $supported);
    }

    private function deserialize(string $content): NodeType
    {
        /*
         * Validate YAML configuration before deserializing it.
         * Low-level validation
         */
        $nodeTypeConfig = Yaml::parse($content);
        $processor = new Processor();
        $processedNodeTypeConfig = $processor->processConfiguration(
            new NodeTypeConfiguration(),
            [
                'node_type' => $nodeTypeConfig,
            ]
        );

        $nodeType = $this->serializer->deserialize(
            Yaml::dump($processedNodeTypeConfig),
            NodeType::class,
            'yaml',
            ['groups' => ['node_type:import']]
        );

        if (!$nodeType instanceof NodeType) {
            throw new \RuntimeException('Deserialized NodeType is not an instance of NodeType');
        }

        /*
         * High level validation once deserialized.
         */
        $violations = $this->validator->validate($nodeType);
        if (count($violations) > 0) {
            throw new ValidationFailedException($nodeType, $violations);
        }

        return $nodeType;
    }
}
