<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

/**
 * Base entity interface which deals with identifier.
 *
 * Every database entity should implement that interface.
 */
interface PersistableInterface
{
    /**
     * Get entity unique identifier.
     */
    public function getId(): int|string|null;
}
