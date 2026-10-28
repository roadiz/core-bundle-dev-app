<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\TreeWalker\Fixtures;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;

class NSPrefetchBlock extends NodesSources
{
    #[\Override]
    public function getNodeTypeName(): string
    {
        return 'PrefetchBlock';
    }
}
