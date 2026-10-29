<?php

declare(strict_types=1);

namespace RZ\Roadiz\Markdown\Tests;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\Markdown\CommonMark;

final class CommonMarkTest extends TestCase
{
    private function createCommonMark(): CommonMark
    {
        $environment = new Environment(['html_input' => 'strip']);
        $environment->addExtension(new CommonMarkCoreExtension());
        $converter = new MarkdownConverter($environment);

        // strip() only relies on the textExtra converter, but the constructor
        // requires all five converters.
        return new CommonMark($converter, $converter, $converter, $converter, $converter);
    }

    /**
     * @dataProvider stripProvider
     */
    public function testStrip(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, $this->createCommonMark()->strip($input));
    }

    /**
     * @return array<array{0: ?string, 1: ?string}>
     */
    public static function stripProvider(): array
    {
        return [
            'null returns null' => [null, null],
            'plain text is untouched' => ['Hello world', 'Hello world'],
            'markdown emphasis is stripped' => ['**Hello** _world_', 'Hello world'],
            'headings are stripped' => ['# Title', 'Title'],
            'links keep their label only' => ['[Roadiz](https://roadiz.io)', 'Roadiz'],
            'hard line break becomes a space' => ["Line1  \nLine2", 'Line1 Line2'],
            'control characters are removed' => ["a\x07b", 'ab'],
            'DEL character is removed' => ["a\x7Fb", 'ab'],
        ];
    }

    public function testStripRemovesHtmlTags(): void
    {
        $result = $this->createCommonMark()->strip('Some **bold** text with a [link](https://roadiz.io).');

        $this->assertStringNotContainsString('<', (string) $result);
        $this->assertStringNotContainsString('>', (string) $result);
    }
}
