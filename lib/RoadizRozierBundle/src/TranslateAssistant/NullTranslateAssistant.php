<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use DeepL\DeepLException;

final readonly class NullTranslateAssistant implements TranslateAssistantInterface
{
    /**
     * @throws DeepLException
     */
    #[\Override]
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        throw new \LogicException('You must configure your translate assistant to use this feature.');
    }

    /**
     * @throws DeepLException
     */
    #[\Override]
    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        throw new \LogicException('You must configure your translate assistant to use this feature.');
    }
}
