<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

interface TranslateAssistantInterface
{
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput;

    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput;

    public function supportRephrase(): bool;
}
