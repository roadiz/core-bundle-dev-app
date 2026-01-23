<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\ORM\Mapping as ORM;

/**
 * Base entity implementing PersistableInterface to offer a unique ID.
 *
 * @deprecated since 2.6, use composition with PersistableInterface and SequentialIdTrait or UuidTrait instead.
 */
#[ORM\MappedSuperclass,
    ORM\Table]
abstract class AbstractEntity implements PersistableInterface
{
    use SequentialIdTrait;
}
