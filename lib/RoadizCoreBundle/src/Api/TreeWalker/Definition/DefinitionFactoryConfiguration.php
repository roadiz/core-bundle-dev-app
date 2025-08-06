<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

final readonly class DefinitionFactoryConfiguration
{
    /**
     * @param class-string $classname
     */
    public function __construct(
        public string $classname,
        public DefinitionFactoryInterface $definitionFactory,
        public bool $onlyVisible,
    ) {
    }
}
