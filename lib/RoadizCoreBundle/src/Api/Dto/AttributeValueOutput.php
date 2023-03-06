<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use RZ\Roadiz\CoreBundle\Model\AttributableInterface;
use RZ\Roadiz\CoreBundle\Model\AttributeInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @deprecated
 */
final class AttributeValueOutput
{
    /**
     * @var AttributeInterface|null
     */
    #[Groups(['attribute'])]
    public ?AttributeInterface $attribute = null;
    /**
     * @var AttributableInterface|null
     */
    #[Groups(['attribute'])]
    public ?AttributableInterface $attributable = null;
    /**
     * @var int|null
     */
    #[Groups(['attribute'])]
    public ?int $type = null;
    /**
     * @var mixed|null
     */
    #[Groups(['attribute'])]
    public $value = null;
    /**
     * @var string|null
     */
    #[Groups(['attribute'])]
    public ?string $label = null;
}
