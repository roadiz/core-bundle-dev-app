<?php

declare(strict_types=1);
namespace RZ\Roadiz\CoreBundle\NodeType;

use RZ\Roadiz\Contracts\NodeType\NodeTypeClassLocatorInterface;
use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

class NodeTypeClassLocator implements NodeTypeClassLocatorInterface
{

    /**
     * @inheritDoc
     */
    public function getSourceEntityClassName(NodeTypeInterface $nodeType): string
    {
        return 'NS'.ucwords($nodeType->getName());
    }

    /**
     * @inheritDoc
     */
    public static function getRepositoryClassName(NodeTypeInterface $nodeType): string
    {
        return 'NS'.ucwords($nodeType->getName()).'Repository';
    }

    /**
     * @inheritDoc
     */
    public function getSourceEntityFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
        return $this->getClassNamespace().'\\'.$this->getSourceEntityClassName($nodeType);
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryFullQualifiedClassName(NodeTypeInterface $nodeType): string
    {
        return $this->getRepositoryNamespace().'\\'.self::getRepositoryClassName($nodeType);
    }

    /**
     * @inheritDoc
     */
    public function getClassNamespace(): string
    {
        return 'App\\GeneratedEntity';
    }

    /**
     * @inheritDoc
     */
    public function getRepositoryNamespace(): string
    {
        return 'App\\GeneratedEntity\\Repository';
    }
}
