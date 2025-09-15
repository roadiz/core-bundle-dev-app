<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Translator;

final readonly class TranslatorOutput
{
    public function __construct(
        public string $originalText,
        public string $translatedText,
        public string $sourceLang,
        public string $targetLang,
    ) {
    }
}
