<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition;

use RZ\TreeWalker\WalkerContextInterface;

interface DefinitionFactoryInterface
{
    public function create(WalkerContextInterface $context, bool $onlyVisible = true): callable;
}
