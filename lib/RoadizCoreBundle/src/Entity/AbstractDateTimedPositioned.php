<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Metadata\ApiFilter;
use Doctrine\Common\Comparable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use RZ\Roadiz\Core\AbstractEntities\AbstractDateTimed;
use RZ\Roadiz\Core\AbstractEntities\PositionedInterface;
use RZ\Roadiz\Core\AbstractEntities\PositionedTrait;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Combined AbstractDateTimed and PositionedTrait.
 */
#[
    ORM\MappedSuperclass,
    ORM\HasLifecycleCallbacks,
    ORM\Table,
    ORM\Index(columns: ['position']),
    ORM\Index(columns: ['created_at']),
    ORM\Index(columns: ['updated_at'])
]
abstract class AbstractDateTimedPositioned extends AbstractDateTimed implements PositionedInterface, Comparable
{
    use PositionedTrait;

    #[
        ORM\Column(type: 'float'),
        Serializer\Groups(['position']),
        Serializer\Type('float'),
        SymfonySerializer\Groups(['position']),
        ApiFilter(RangeFilter::class),
        ApiFilter(NumericFilter::class)
    ]
    protected float $position = 0.0;
}
