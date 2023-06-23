<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\Common\Comparable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Combined AbstractDateTimed and PositionedTrait.
 */
#[
    ORM\MappedSuperclass,
    ORM\HasLifecycleCallbacks,
    ORM\Table,
    ORM\Index(columns: ["position"]),
    ORM\Index(columns: ["created_at"]),
    ORM\Index(columns: ["updated_at"])
]
abstract class AbstractDateTimedPositioned extends AbstractDateTimed implements PositionedInterface, Comparable
{
    use PositionedTrait;

    #[
        ORM\Column(type: "float"),
        Serializer\Groups(["position"]),
        Serializer\Type("float"),
        SymfonySerializer\Groups(["position"])
    ]
    protected float $position = 0.0;
}
