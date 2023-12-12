<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

final class DefinitionFactoryConfiguration
{
    /**
     * @var class-string
     */
    public string $classname;
    public bool $onlyVisible;
    public DefinitionFactoryInterface $definitionFactory;

    /**
     * @param class-string $classname
     * @param DefinitionFactoryInterface $definitionFactory
     * @param bool $onlyVisible
     */
    public function __construct(string $classname, DefinitionFactoryInterface $definitionFactory, bool $onlyVisible)
    {
        $this->classname = $classname;
        $this->onlyVisible = $onlyVisible;
        $this->definitionFactory = $definitionFactory;
    }
}
