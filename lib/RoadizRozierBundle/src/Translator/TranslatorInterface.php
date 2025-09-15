<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Translator;

interface TranslatorInterface
{
    public function translate(TranslatorDto $translatorDto): TranslatorOutput;
    public function rephrase(TranslatorDto $translatorDto): TranslatorOutput;
}
