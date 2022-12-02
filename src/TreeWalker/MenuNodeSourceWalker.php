<?php

declare(strict_types=1);

namespace App\TreeWalker;

use App\GeneratedEntity\NSMenu;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\Definition\MultiTypeChildrenDefinition;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\NodeSourceWalkerContext;
use RZ\TreeWalker\AbstractCycleAwareWalker;

/**
 * @package App\TreeWalker
 */
final class MenuNodeSourceWalker extends AbstractCycleAwareWalker
{
    protected function initializeDefinitions(): void
    {
        if ($this->isRoot()) {
            $context = $this->getContext();
            if ($context instanceof NodeSourceWalkerContext) {
                $this->addDefinition(
                    NSMenu::class,
                    new MultiTypeChildrenDefinition($context, [
                        'Page',
                        'Menu',
                        'MenuLink',
                    ])
                );
            }
        }
    }
}
