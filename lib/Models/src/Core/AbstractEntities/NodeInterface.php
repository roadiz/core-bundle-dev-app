<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use RZ\Roadiz\Contracts\NodeType\NodeTypeInterface;

/**
 * Node interface to be implemented by Node Doctrine entity and DTOs.
 */
interface NodeInterface extends PersistableInterface
{
    public function getChildrenOrder(): string;

    public function getChildrenOrderDirection(): string;

    public function isDraft(): bool;

    public function isPending(): bool;

    public function isPublished(): bool;

    public function getNodeType(): NodeTypeInterface;
}
