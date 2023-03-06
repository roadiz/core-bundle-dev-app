<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @deprecated
 */
final class CustomFormOutput
{
    /**
     * @var string|null
     */
    #[Groups(['custom_form', 'document_display'])]
    public ?string $slug = null;
    /**
     * @var string|null
     */
    #[Groups(['custom_form', 'document_display'])]
    public ?string $name = null;
    /**
     * @var string|null
     */
    #[Groups(['custom_form', 'document_display'])]
    public ?string $color = null;
    /**
     * @var string|null
     */
    #[Groups(['custom_form', 'document_display'])]
    public ?string $description = null;
    /**
     * @var bool
     */
    #[Groups(['custom_form', 'document_display'])]
    public bool $open = false;
    /**
     * @var string|null
     */
    #[Groups(['custom_form', 'document_display'])]
    public ?string $definitionUrl = null;
    /**
     * @var string|null
     */
    #[Groups(['custom_form', 'document_display'])]
    public ?string $postUrl = null;
}
