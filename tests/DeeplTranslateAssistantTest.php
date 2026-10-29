<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\RozierBundle\TranslateAssistant\DeeplTranslateAssistant;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantAccountException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInput;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Offline assertions only: everything here must hold without reaching DeepL.
 */
final class DeeplTranslateAssistantTest extends TestCase
{
    public function testMissingKeyIsAnAccountFailureNotATransportOne(): void
    {
        /*
         * The distinction is what stops Messenger from retrying: a missing key cannot heal,
         * a transport failure can.
         */
        $this->expectException(TranslateAssistantAccountException::class);

        $this->assistant()->translate(new TranslateAssistantInput(text: 'Bonjour', targetLang: 'en'));
    }

    public function testMissingKeyAlsoFailsRephraseAgnostically(): void
    {
        $this->expectException(TranslateAssistantAccountException::class);

        $this->assistant()->rephrase(new TranslateAssistantInput(text: 'Bonjour', targetLang: 'en'));
    }

    public function testUsageStaysNullWithoutKeyInsteadOfThrowing(): void
    {
        // Views call usage() without knowing which provider is wired: it must never throw.
        $this->assertNull($this->assistant()->usage());
    }

    public function testUsagePrefersTheApiKeyQuotaOverTheAccountOne(): void
    {
        /*
         * deepl-php does not expose api_key_character_* yet, and a PRO account reports both:
         * showing the account-wide count would misreport what this key may still consume.
         */
        $client = new MockHttpClient(new MockResponse(json_encode([
            'character_count' => 5941580,
            'character_limit' => 1000000000000,
            'api_key_character_count' => 636,
            'api_key_character_limit' => 500000,
        ], JSON_THROW_ON_ERROR)));

        $usage = $this->assistant('key', $client)->usage();

        $this->assertSame(636, $usage?->characterCount);
        $this->assertSame(500000, $usage?->characterLimit);
    }

    public function testUsageFallsBackToTheAccountQuotaWhenTheKeyOneIsAbsent(): void
    {
        $client = new MockHttpClient(new MockResponse(json_encode([
            'character_count' => 180,
            'character_limit' => 500000,
        ], JSON_THROW_ON_ERROR)));

        $usage = $this->assistant('key', $client)->usage();

        $this->assertSame(180, $usage?->characterCount);
        $this->assertSame(500000, $usage?->characterLimit);
    }

    public function testUsageStaysNullWhenDeeplIsUnreachable(): void
    {
        $client = new MockHttpClient(new MockResponse('', ['http_code' => 500]));

        $this->assertNull($this->assistant('key', $client)->usage());
    }

    private function assistant(string $apiKey = '', ?HttpClientInterface $httpClient = null): DeeplTranslateAssistant
    {
        return new DeeplTranslateAssistant(new ArrayAdapter(), $httpClient ?? new MockHttpClient(), $apiKey);
    }
}
