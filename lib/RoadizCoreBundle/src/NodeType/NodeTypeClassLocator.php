<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;

final readonly class NodeTypeClassLocator implements NodeTypeClassLocatorInterface
{
    /**
     * @param non-empty-string $generatedClassNamespace
     * @param non-empty-string $generatedRepositoryNamespace
     */
    public function __construct(
        private string $generatedClassNamespace,
        private string $generatedRepositoryNamespace,
    ) {
    }

    #[\Override]
    public function getSourceEntityClassName(NodeTypeInterface $nodeType): string
    {
        return 'NS'.ucwords($nodeType->getName());
    }

    #[\Override]
    public function getRepositoryClassName(NodeTypeInterface $nodeType): string
    {
        return 'NS'.ucwords($nodeType->getName()).'Repository';
    }

    /**
     * @return class-string<NodesSources>
     */
    #[\Override]
    public function getSourceEntityFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
        /* @phpstan-ignore-next-line */
        return $this->getClassNamespace().'\\'.$this->getSourceEntityClassName($nodeType);
    }

    /**
     * @return class-string<NodesSourcesRepository>
     */
    #[\Override]
    public function getRepositoryFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
        /* @phpstan-ignore-next-line */
        return $this->getRepositoryNamespace().'\\'.self::getRepositoryClassName($nodeType);
    }

    #[\Override]
    public function getClassNamespace(): string
    {
        return $this->generatedClassNamespace;
    }

    #[\Override]
    public function getRepositoryNamespace(): string
    {
        return $this->generatedRepositoryNamespace;
    }
}
