<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\NodeType;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class NodeTypeFilesRepository implements NodeTypeRepositoryInterface
{
    public function __construct(
        private string $nodeTypesDir,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @throws \Exception
     *
     * @return NodeType[]
     */
    public function findAll(): array
    {
        $finder = new Finder();
        $finder->files()->in($this->nodeTypesDir);
        if (!$finder->hasResults()) {
            throw new \Exception('No files exist in this folder : '.$this->nodeTypesDir);
        }
        $nodeTypes = [];

        foreach ($finder as $file) {
            $content = $this->checkFile($file);
            if (null === $content) {
                continue;
            }
            $nodeTypes[] = $this->deserialize($content);
        }

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

        $content = $this->checkFile($firstFile);
        if (null === $content) {
            return null;
        }

        return $this->deserialize($content);
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
            ucfirst(mb_strtoupper($name)),
            $name.'.yml',
            $name.'.yaml',
            ucfirst(mb_strtolower($name)).'.yml',
            ucfirst(mb_strtolower($name)).'.yaml',
            ucfirst(mb_strtoupper($name)).'.yml',
            ucfirst(mb_strtoupper($name)).'.yaml',
        ];

        return in_array($fileName, $supported);
    }

    private function deserialize(string $content): NodeType
    {
        $nodeType = $this->serializer->deserialize(
            $content,
            NodeType::class,
            'yaml',
            ['groups' => ['node_type:import', 'position']]
        );

        $violations = $this->validator->validate($nodeType);
        if (count($violations) > 0) {
            throw new ValidationFailedException($nodeType, $violations);
        }

        return $nodeType;
    }
}
