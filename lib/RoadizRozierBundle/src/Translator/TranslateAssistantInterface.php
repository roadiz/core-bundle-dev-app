<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Translator;

interface TranslateAssistantInterface
{
    public function translate(TranslateAssistantDto $translatorDto): TranslateAssistantOutput;
    public function rephrase(TranslateAssistantDto $translatorDto): TranslateAssistantOutput;
}
