<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\RozierBundle\TranslateAssistant\DeeplTranslateAssistant;
use RZ\Roadiz\RozierBundle\TranslateAssistant\Exception\TranslateAssistantAccountException;
use RZ\Roadiz\RozierBundle\TranslateAssistant\TranslateAssistantInput;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

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

    private function assistant(): DeeplTranslateAssistant
    {
        return new DeeplTranslateAssistant(new ArrayAdapter(), '');
    }
}
