<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\Tag;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @deprecated
 */
final class TagOutput
{
    /**
     * @var string
     */
    #[Groups(['tag', 'tag_base'])]
    public string $slug = '';
    /**
     * @var string|null
     */
    #[Groups(['tag', 'tag_base'])]
    public ?string $name = null;
    /**
     * @var string|null
     */
    #[Groups(['tag'])]
    public ?string $description = null;
    /**
     * @var string|null
     */
    #[Groups(['tag', 'tag_base'])]
    public ?string $color = null;
    /**
     * @var bool
     */
    #[Groups(['tag', 'tag_base'])]
    public bool $visible = false;
    /**
     * @var array<Document>
     */
    #[Groups(['tag', 'tag_base'])]
    public array $documents = [];
    /**
     * @var Tag|null
     */
    #[Groups(['tag', 'tag_base'])]
    #[MaxDepth(1)]
    public ?Tag $parent = null;
    /**
     * @Groups({"tag", "tag_base"})
     */
    public ?float $position = null;
}
