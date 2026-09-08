<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\TranslateAssistant;

use DeepL\AuthorizationException;
use DeepL\ConnectionException;
use DeepL\DeepLClient;
use DeepL\DeepLException;
use DeepL\Language;
use DeepL\QuotaExceededException;
use DeepL\TooManyRequestsException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantAccountException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantTransportException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantUsageException;

final readonly class DeeplTranslateAssistant implements TranslateAssistantInterface
{
    public function __construct(
        private CacheItemPoolInterface $cache,
        private string $apiKey,
    ) {
    }

    /**
     * @throws TranslateAssistantException
     * @throws InvalidArgumentException
     */
    #[\Override]
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        if (empty($this->apiKey)) {
            throw new TranslateAssistantAccountException('DeepL API key is required.');
        }

        try {
            $deeplClient = new DeepLClient($this->apiKey);

            $this->denyNotAvailableLanguages($this->transformTargetLang($translatorDto->targetLang), $deeplClient, 'translate');

            $result = $deeplClient->translateText(
                $translatorDto->text,
                $translatorDto->sourceLang,
                $this->transformTargetLang($translatorDto->targetLang),
                $translatorDto->options ?? []
            );
        } catch (DeepLException $exception) {
            throw self::asAgnosticException($exception);
        }

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
     * @throws TranslateAssistantException|InvalidArgumentException
     */
    #[\Override]
    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        if (empty($this->apiKey)) {
            throw new TranslateAssistantAccountException('DeepL API key is required.');
        }

        try {
            $deeplClient = new DeepLClient($this->apiKey);

            $this->denyNotAvailableLanguages($translatorDto->targetLang, $deeplClient, 'rephrase');

            $result = $deeplClient->rephraseText(
                $translatorDto->text,
                $this->transformTargetLang($translatorDto->targetLang),
                $translatorDto->options ?? []
            );
        } catch (DeepLException $exception) {
            throw self::asAgnosticException($exception);
        }

        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: is_array($result) ? $result[0]->text : $result->text,
            sourceLang: $translatorDto->sourceLang ?? '',
            targetLang: $translatorDto->targetLang,
        );
    }

    /**
     * Maps a DeepL failure onto the provider-agnostic hierarchy, so callers can decide whether
     * retrying is worth anything without knowing DeepL exists.
     */
    private static function asAgnosticException(DeepLException $exception): TranslateAssistantException
    {
        return match (true) {
            $exception instanceof QuotaExceededException => new TranslateAssistantUsageException($exception->getMessage(), previous: $exception),
            $exception instanceof AuthorizationException,
            $exception instanceof TooManyRequestsException=> new TranslateAssistantAccountException($exception->getMessage(), previous: $exception),
            $exception instanceof ConnectionException, => new TranslateAssistantTransportException($exception->getMessage(), previous: $exception),
            default => new TranslateAssistantException($exception->getMessage(), previous: $exception),
        };
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

    #[\Override]
    public function usage(): ?TranslateAssistantUsage
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            // Shorter TTL than denyNotAvailableLanguages: the quota moves at every translation.
            $cacheItem = $this->cache->getItem('DeeplTranslateAssistant_usage');
            if (!$cacheItem->isHit()) {
                $character = (new DeepLClient($this->apiKey))->getUsage()->character;
                $cacheItem->set(null !== $character ? new TranslateAssistantUsage($character->count, $character->limit) : null);
                $cacheItem->expiresAfter(300);
                $this->cache->save($cacheItem);
            }

            $usage = $cacheItem->get();

            return $usage instanceof TranslateAssistantUsage ? $usage : null;
        } catch (DeepLException|InvalidArgumentException) {
            // An unreachable quota must break neither the dashboard nor an edit form.
            return null;
        }
    }
}
