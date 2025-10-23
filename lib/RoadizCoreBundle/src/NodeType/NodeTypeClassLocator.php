<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

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

    #[\Override]
    public function getSourceEntityFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
        return $this->getClassNamespace().'\\'.$this->getSourceEntityClassName($nodeType);
    }

    #[\Override]
    public function getRepositoryFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
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
