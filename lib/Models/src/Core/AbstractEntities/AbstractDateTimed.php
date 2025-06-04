<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\ORM\Mapping as ORM;

/**
 * An AbstractEntity with datetime fields to keep track of time with your items.
 *
 * @deprecated since 2.6, use composition with DateTimedTrait instead.
 */
#[
    ORM\MappedSuperclass,
    ORM\HasLifecycleCallbacks,
    ORM\Table,
    ORM\Index(columns: ['created_at']),
    ORM\Index(columns: ['updated_at']),
]
abstract class AbstractDateTimed extends AbstractEntity implements DateTimedInterface
{
    use DateTimedTrait;
}
