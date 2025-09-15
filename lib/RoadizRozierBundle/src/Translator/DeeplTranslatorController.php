<?php

declare(strict_types=1);

namespace RZ\Roadiz\RozierBundle\Translator;

use DeepL\DeepLClient;
use DeepL\DeepLException;
use DeepL\Language;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[AsController]
final readonly class DeeplTranslatorController implements TranslatorInterface
{
    public function __construct(
        private ?string $apiKey = null,
    ) {
    }

    /**
     * @throws DeepLException
     */
    public function translate(
        #[MapRequestPayload] TranslatorDto $translatorDto,
    ): TranslatorOutput {
        if (null === $this->apiKey) {
            throw new DeepLException('DeepL api key is required.');
        }

        // $deeplClient = new DeepLClient($this->apiKey);

        // if (!in_array($translatorDto->targetLang, array_map(fn (Language $language) => $language->code, $deeplClient->getTargetLanguages()))) {
        //     throw new DeepLException('Invalid target language.');
        // }

        // $result = $deeplClient->translateText($translatorDto->text, $translatorDto->sourceLang, $translatorDto->targetLang, $translatorDto->options);

        return new TranslatorOutput(
            $translatorDto->text,
            // $result->text,
            'Bonjour monde',
            // $result->detectedSourceLang,
            'en-GB',
            $translatorDto->targetLang,
        );
    }
}
