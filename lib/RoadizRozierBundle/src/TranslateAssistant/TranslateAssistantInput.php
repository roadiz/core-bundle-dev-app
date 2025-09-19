<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

final readonly class TranslateAssistantInput
{
    public function __construct(
        public string $text,
        public string $targetLang,
        public ?string $sourceLang = null,
        public ?array $options = [],
    ) {
    }
}
