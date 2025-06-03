<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Symfony\Component\Uid\Uuid;

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
    public function getId(): Uuid|int|string|null;
}
