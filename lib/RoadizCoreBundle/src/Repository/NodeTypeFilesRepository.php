<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class NodeTypeFilesRepository implements NodeTypeRepositoryInterface
{
    public function __construct(
        private string $nodesTypesDir,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function findAll(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->nodesTypesDir);
        if (!$finder->hasResults()) {
            throw new \Exception('No files exist in this folder : '.$this->nodesTypesDir);
        }
        $nodeTypes = [];

        foreach ($finder as $file) {
            $content = $this->checkFile($file);
            if (null === $content) {
                continue;
            }
            $nodeTypes[] = $this->serializer->deserialize(
                $content,
                NodeType::class,
                'yaml',
                ['groups' => ['node_type:import', 'position']]
            );
        }

        return $nodeTypes;
    }

    /**
     * @throws \Exception
     */
    public function findOneByName(string $name): ?NodeType
    {
        $finder = new Finder();
        $finder->files()->in($this->nodesTypesDir);
        if (!$finder->hasResults()) {
            throw new \Exception('No files exist in this folder : '.$this->nodesTypesDir);
        }

        $finder->filter(function (\SplFileInfo $file) use ($name) {
            return $this->supportName($file->getBasename(), $name);
        });

        $iterator = $finder->getIterator();
        $iterator->rewind();
        $firstFile = $iterator->current();

        $content = $this->checkFile($firstFile);
        if (null === $content) {
            return null;
        }

        return $this->serializer->deserialize(
            $content,
            NodeType::class,
            'yaml',
            ['groups' => ['node_type:import', 'position']]
        );
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
            mb_strtolower($name),
            mb_strtoupper($name),
            $name.'.yml',
            $name.'.yaml',
            mb_strtolower($name.'.yml'),
            mb_strtolower($name.'.yaml'),
            mb_strtoupper($name.'.yml'),
            mb_strtoupper($name.'.yaml'),
        ];

        return in_array($fileName, $supported);
    }
}
