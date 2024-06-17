<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use RZ\Roadiz\CoreBundle\Entity\Node;

interface NodeOffspringResolverInterface
{
    /**
     * @param Node $ancestor
     * @return array<int>
     */
    public function getAllOffspringIds(Node $ancestor): array;
}
