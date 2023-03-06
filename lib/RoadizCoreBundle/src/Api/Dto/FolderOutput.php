<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @deprecated
 */
final class FolderOutput
{
    /**
     * @var string|null
     */
    #[Groups(['folder', 'document', 'document_display'])]
    public ?string $slug = null;
    /**
     * @var string|null
     */
    #[Groups(['folder', 'document', 'document_display'])]
    public ?string $name = null;
    /**
     * @var bool
     */
    #[Groups(['folder', 'document', 'document_display'])]
    public bool $visible = false;
    /**
     * @Groups({"folder", "document", "document_display"})
     */
    public ?float $position = null;
}
