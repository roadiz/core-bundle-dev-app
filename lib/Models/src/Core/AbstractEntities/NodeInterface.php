<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

/**
 * Node interface to be implemented by Node Doctrine entity and DTOs.
 */
interface NodeInterface extends PersistableInterface
{
    #[\Override]
    public function getId(): ?int;

    public function getChildrenOrder(): string;

    public function getChildrenOrderDirection(): string;

    public function isDraft(): bool;

    public function isPending(): bool;

    public function isPublished(): bool;

    public function getNodeTypeName(): string;
}
