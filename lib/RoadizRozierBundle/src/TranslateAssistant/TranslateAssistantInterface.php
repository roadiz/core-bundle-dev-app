<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantException;

interface TranslateAssistantInterface
{
    /**
     * Implementations must translate provider errors into the TranslateAssistantException
     * hierarchy: callers decide whether to retry from the exception type alone, and must never
     * have to know which provider is wired.
     *
     * @throws TranslateAssistantException
     */
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput;

    /**
     * @throws TranslateAssistantException
     */
    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput;

    public function supportRephrase(): bool;

    /**
     * @return TranslateAssistantUsage|null Null when the provider has no quota notion, or when it cannot be read
     */
    public function usage(): ?TranslateAssistantUsage;
}
