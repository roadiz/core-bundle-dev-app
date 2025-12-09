<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use DeepL\DeepLClient;
use DeepL\DeepLException;
use DeepL\Language;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

final readonly class DeeplTranslateAssistant implements TranslateAssistantInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private string $apiKey,
    ) {
    }

    /**
     * @throws DeepLException
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        if (empty($this->apiKey)) {
            throw new DeepLException('DeepL api key is required.');
        }

        $deeplClient = new DeepLClient($this->apiKey);

        $this->denyNotAvailableLanguages($this->transformTargetLang($translatorDto->targetLang), $deeplClient, 'translate');

        $result = $deeplClient->translateText(
            $translatorDto->text,
            $translatorDto->sourceLang,
            $this->transformTargetLang($translatorDto->targetLang),
            $translatorDto->options ?? []
        );

        if (is_array($result)) {
            $result = $result[0];
        }

        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: $result->text,
            sourceLang: $result->detectedSourceLang,
            targetLang: $translatorDto->targetLang,
        );
    }

    /**
     * This feature requires a PRO Deepl Api-token.
     * https://developers.deepl.com/api-reference/improve-text/deepl-write-api-service-specification-updates.
     *
     * @throws DeepLException|InvalidArgumentException
     */
    #[\Override]
    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        if (empty($this->apiKey)) {
            throw new DeepLException('DeepL api key is required.');
        }

        $deeplClient = new DeepLClient($this->apiKey);

        $this->denyNotAvailableLanguages($translatorDto->targetLang, $deeplClient, 'rephrase');

        $result = $deeplClient->rephraseText(
            $translatorDto->text,
            $this->transformTargetLang($translatorDto->targetLang),
            $translatorDto->options ?? []
        );

        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: is_array($result) ? $result[0]->text : $result->text,
            sourceLang: $translatorDto->sourceLang ?? '',
            targetLang: $translatorDto->targetLang,
        );
    }

    private function transformTargetLang(string $targetLang): string
    {
        return match ($targetLang) {
            'en' => 'en-GB',
            'pt' => 'pt-PT',
            default => $targetLang,
        };
    }

    /**
     * @throws DeepLException
     * @throws InvalidArgumentException
     */
    private function denyNotAvailableLanguages(string $targetLanguage, DeepLClient $deeplClient, string $method): void
    {
        $languageAvailableCacheItem = $this->cache->getItem('DeeplTranslateAssistant_targetLanguages_'.$method.'_'.$targetLanguage);

        if (!$languageAvailableCacheItem->isHit()) {
            $languageAvailableCacheItem->set(
                in_array(
                    mb_strtoupper($this->transformTargetLang($targetLanguage)),
                    array_map(fn (Language $language) => $language->code, $deeplClient->getTargetLanguages())
                )
            );
            $languageAvailableCacheItem->expiresAfter(3600);
            $this->cache->save($languageAvailableCacheItem);
        }

        if (!$languageAvailableCacheItem->get()) {
            throw new DeepLException('Invalid target language.');
        }
    }

    #[\Override]
    public function supportRephrase(): bool
    {
        return !str_ends_with($this->apiKey, ':fx');
    }
}
