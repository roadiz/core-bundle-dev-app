# Translate Assistant

## Overview

The Translate Assistant powers automatic Markdown translation and rephrasing in the back office. Today, Roadiz ships with a DeepL implementation only. You can swap in another provider by implementing the Translate Assistant contract and wiring your service in the container.

## How it works

- The back office submits Markdown text, source language, and target language.
- Roadiz calls a service implementing `TranslateAssistantInterface`.
- The service returns a `TranslateAssistantOutput` with the translated text and language metadata.
- Rephrase is optional and depends on the provider capabilities.

## Core contract

Your service must implement `RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface`:

```php
<?php

namespace App\TranslateAssistant;

use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInput;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantOutput;

final readonly class CustomTranslateAssistant implements TranslateAssistantInterface
{
    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: '...',
            sourceLang: $translatorDto->sourceLang ?? '',
            targetLang: $translatorDto->targetLang,
        );
    }

    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: '...',
            sourceLang: $translatorDto->sourceLang ?? '',
            targetLang: $translatorDto->targetLang,
        );
    }

    public function supportRephrase(): bool
    {
        return false;
    }
}
```

### Input/Output DTOs

- `TranslateAssistantInput`
  - `text` (string)
  - `targetLang` (string)
  - `sourceLang` (string|null)
  - `options` (array|null)
- `TranslateAssistantOutput`
  - `originalText` (string)
  - `translatedText` (string)
  - `sourceLang` (string)
  - `targetLang` (string)

## Wiring your provider

Bind your service to the interface in your project configuration so the back office uses it:

```yaml
# config/services.yaml
services:
    App\TranslateAssistant\CustomTranslateAssistant:
        autowire: true
        autoconfigure: true

    RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface:
        alias: App\TranslateAssistant\CustomTranslateAssistant
```

If you remove the DeepL API key in `roadiz_rozier.translate_assistant`, Roadiz falls back to a null assistant. Your custom service should be registered unconditionally or guarded by your own config.

## ChatGPT example (OpenAI)

Example provider using the Chat Completions API with the `gpt-4o-mini` model and a plain HTTP client:

```php
<?php

namespace App\TranslateAssistant;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInput;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantOutput;

final readonly class OpenAiTranslateAssistant implements TranslateAssistantInterface
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiKey,
        private string $baseUrl = 'https://api.openai.com/v1',
        private string $model = 'gpt-4o-mini',
    ) {
    }

    public function translate(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        $prompt = sprintf(
            'Translate this Markdown to %s. Return Markdown only.\n\n%s',
            $translatorDto->targetLang,
            $translatorDto->text
        );

        $translatedText = $this->request($prompt, $translatorDto->sourceLang);

        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: $translatedText,
            sourceLang: $translatorDto->sourceLang ?? '',
            targetLang: $translatorDto->targetLang,
        );
    }

    public function rephrase(TranslateAssistantInput $translatorDto): TranslateAssistantOutput
    {
        $prompt = sprintf(
            'Rephrase this Markdown for clarity. Keep the language as %s. Return Markdown only.\n\n%s',
            $translatorDto->targetLang,
            $translatorDto->text
        );

        $translatedText = $this->request($prompt, $translatorDto->sourceLang);

        return new TranslateAssistantOutput(
            originalText: $translatorDto->text,
            translatedText: $translatedText,
            sourceLang: $translatorDto->sourceLang ?? '',
            targetLang: $translatorDto->targetLang,
        );
    }

    public function supportRephrase(): bool
    {
        return true;
    }

    private function request(string $prompt, ?string $sourceLang): string
    {
        $response = $this->httpClient->request('POST', $this->baseUrl.'/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->apiKey,
            ],
            'json' => [
                'model' => $this->model,
                'temperature' => 0.2,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a translation assistant for Markdown content.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ],
        ]);

        $payload = $response->toArray();

        return trim((string) ($payload['choices'][0]['message']['content'] ?? ''));
    }
}
```

Service wiring and configuration:

```yaml
# config/services.yaml
services:
    App\TranslateAssistant\OpenAiTranslateAssistant:
        arguments:
            $apiKey: '%env(OPENAI_API_KEY)%'
            $baseUrl: '%env(default:OPENAI_API_BASE_URL:OPENAI_API_BASE_URL)%'
            $model: '%env(default:OPENAI_TRANSLATE_MODEL:OPENAI_TRANSLATE_MODEL)%'

    RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInterface:
        alias: App\TranslateAssistant\OpenAiTranslateAssistant
```

```dotenv
OPENAI_API_KEY=your-key
OPENAI_API_BASE_URL=https://api.openai.com/v1
OPENAI_TRANSLATE_MODEL=gpt-4o-mini
```

Be mindful of rate limits and usage costs when enabling this in production.

## DeepL note

DeepL remains the only built-in provider at the moment. If you need another service, implement the interface and alias it as shown above.
