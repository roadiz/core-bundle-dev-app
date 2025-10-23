<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;
use RZ\Roadiz\CoreBundle\Entity\NodesSources;
use RZ\Roadiz\CoreBundle\Repository\NodesSourcesRepository;

class NodeTypeClassLocator implements NodeTypeClassLocatorInterface
{
    #[\Override]
    public function getSourceEntityClassName(NodeTypeInterface $nodeType): string
    {
        return 'NS'.ucwords($nodeType->getName());
    }

    #[\Override]
    public static function getRepositoryClassName(NodeTypeInterface $nodeType): string
    {
        return 'NS'.ucwords($nodeType->getName()).'Repository';
    }

    /**
     * @param NodeTypeInterface $nodeType
     * @return class-string<NodesSources>
     */
    #[\Override]
    public function getSourceEntityFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
        /* @phpstan-ignore-next-line */
        return $this->getClassNamespace().'\\'.$this->getSourceEntityClassName($nodeType);
    }

    /**
     * @param NodeTypeInterface $nodeType
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
        return 'App\\GeneratedEntity';
    }

    #[\Override]
    public function getRepositoryNamespace(): string
    {
        return 'App\\GeneratedEntity\\Repository';
    }
}
