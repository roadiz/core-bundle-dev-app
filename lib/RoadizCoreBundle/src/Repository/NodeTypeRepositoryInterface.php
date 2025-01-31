<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Repository;

use RZ\Roadiz\CoreBundle\Entity\NodeType;

interface NodeTypeRepositoryInterface
{
    /** @return NodeType[] */
    public function findAll(): array;

    public function findOneByName(string $name): ?NodeType;
}
