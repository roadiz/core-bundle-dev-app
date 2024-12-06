<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Node;

use RZ\Roadiz\Core\AbstractEntities\NodeInterface;

interface NodeOffspringResolverInterface
{
    /**
     * @return array<int>
     */
    public function getAllOffspringIds(NodeInterface $ancestor): array;
}
