<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\TreeWalker;

use RZ\TreeWalker\WalkerContextInterface;

interface WalkerContextFactoryInterface
{
    public function createWalkerContext(): WalkerContextInterface;
}
