<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

/**
 * Node interface to be implemented by Node Doctrine entity and DTOs.
 */
interface NodeInterface extends PersistableInterface
{
    public function getChildrenOrder(): string;

    public function getChildrenOrderDirection(): string;
}
