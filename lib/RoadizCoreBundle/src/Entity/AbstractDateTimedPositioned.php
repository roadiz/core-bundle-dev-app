<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RZ\Roadiz\Core\AbstractEntities\DateTimedInterface;
use RZ\Roadiz\Core\AbstractEntities\DateTimedTrait;
use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedTrait;
use RZ\Roadiz\Core\AbstractEntities\SequentialIdTrait;

/**
 * Combined AbstractDateTimed and PositionedTrait.
 *
 * @deprecated Use composition instead, use PositionedTrait and DateTimedTrait
 */
#[ORM\MappedSuperclass,
    ORM\HasLifecycleCallbacks,
    ORM\Table,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['created_at']),
    ORM\Index(columns: ['updated_at'])]
abstract class AbstractDateTimedPositioned implements PositionedInterface, DateTimedInterface, PersistableInterface
{
    use SequentialIdTrait;
    use DateTimedTrait;
    use PositionedTrait;
}
