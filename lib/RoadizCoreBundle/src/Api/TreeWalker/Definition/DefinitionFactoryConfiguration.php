<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

final class DefinitionFactoryConfiguration
{
    /**
     * @param class-string $classname
     */
    public function __construct(
        public readonly string $classname,
        public readonly DefinitionFactoryInterface $definitionFactory,
        public readonly bool $onlyVisible,
    ) {
    }
}
