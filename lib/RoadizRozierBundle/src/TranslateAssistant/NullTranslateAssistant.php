<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

final readonly class NullTranslateAssistant implements TranslateAssistantInterface
{
    #[\Override]
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        throw new \LogicException('NullTranslateAssistant cannot be used to translate.');
    }

    #[\Override]
    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        throw new \LogicException('NullTranslateAssistant cannot be used to rephrase.');
    }

    #[\Override]
    public function supportRephrase(): bool
    {
        return false;
    }
}
