<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Translator;

final readonly class TranslatorDto
{
    public function __construct(
        public string $text,
        public string $targetLang,
        public ?string $sourceLang = null,
        public ?array $options = [],
    ) {
    }
}
