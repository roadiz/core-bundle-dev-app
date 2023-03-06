<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\Dto;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @deprecated
 */
final class TranslationOutput
{
    /**
     * @var string
     */
    #[Groups(['translation', 'translation_base'])]
    public string $name = '';
    /**
     * @var string
     */
    #[Groups(['translation', 'translation_base'])]
    public string $locale = '';
    /**
     * @var bool
     */
    #[Groups(['translation'])]
    public bool $available = false;
    /**
     * @var bool
     */
    #[Groups(['translation'])]
    public bool $defaultTranslation = false;
}
