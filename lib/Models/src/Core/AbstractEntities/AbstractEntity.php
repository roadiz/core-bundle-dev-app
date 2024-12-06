<?php

declare(strict_types=1);

namespace RZ\Roadiz\Core\AbstractEntities;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * Base entity implementing PersistableInterface to offer a unique ID.
 */
#[
    ORM\MappedSuperclass,
    ORM\Table
]
abstract class AbstractEntity implements PersistableInterface
{
    #[
        ORM\Id,
        ORM\Column(type: 'integer'),
        ORM\GeneratedValue,
        Serializer\Groups(['id']),
        Serializer\Type('integer'),
        SymfonySerializer\Groups(['id'])
    ]
    protected int|string|null $id = null;

    public function getId(): int|string|null
    {
        return $this->id;
    }

    public function setId(int|string|null $id): self
    {
        $this->id = $id;

        return $this;
    }
}
