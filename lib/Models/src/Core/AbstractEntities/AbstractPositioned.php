<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\Common\Comparable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute as Serializer;

/**
 * Combined AbstractEntity and PositionedTrait.
 */
#[
    ORM\MappedSuperclass,
    ORM\HasLifecycleCallbacks,
    ORM\Table,
    ORM\Index(columns: ['position'])
]
abstract class AbstractPositioned extends AbstractEntity implements PositionedInterface, Comparable
{
    use PositionedTrait;

    #[
        ORM\Column(type: 'float'),
        Serializer\Groups(['position']),
    ]
    protected float $position = 0.0;
}
