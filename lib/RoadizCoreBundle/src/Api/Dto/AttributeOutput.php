<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Model\AttributeGroupInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @deprecated
 */
final class AttributeOutput
{
    /**
     * @var int|null
     */
    #[Groups(['attribute'])]
    public ?int $type = null;
    /**
     * @var string|null
     */
    #[Groups(['attribute'])]
    public ?string $name = null;
    /**
     * @var bool
     */
    #[Groups(['attribute'])]
    public bool $searchable = false;
    /**
     * @var string|null
     */
    #[Groups(['attribute'])]
    public ?string $code = null;
    /**
     * @var string|null
     */
    #[Groups(['attribute'])]
    public ?string $color = null;
    /**
     * @var AttributeGroupInterface|null
     */
    #[Groups(['attribute'])]
    public ?AttributeGroupInterface $group = null;
    /**
     * @var array<Document>
     */
    #[Groups(['attribute'])]
    public array $documents = [];
}
