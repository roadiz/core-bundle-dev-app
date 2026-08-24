<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\TwigExtension;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\TwigExtension\RoadizExtension;

final class RoadizExtensionTest extends TestCase
{
    /**
     * @dataProvider provideSafeValues
     */
    public function testSafeCssColorValuesAreAccepted(string $value): void
    {
        $this->assertTrue(RoadizExtension::isSafeCssColorValue($value));
    }

    public static function provideSafeValues(): array
    {
        return [
            ['#d43ed1'],
            ['#fff'],
            ['red'],
            ['rebeccapurple'],
            ['rgb(220, 20, 60)'],
            ['rgba(220, 20, 60, 0.5)'],
            ['hsl(348deg 83% 47%)'],
        ];
    }

    /**
     * @dataProvider provideUnsafeValues
     */
    public function testUnsafeCssColorValuesAreRejected(?string $value): void
    {
        $this->assertFalse(RoadizExtension::isSafeCssColorValue($value));
    }

    public static function provideUnsafeValues(): array
    {
        return [
            [null],
            [''],
            ['red;}</style><script>alert(1)</script>'],
            ['"><script>alert(1)</script>'],
            ["red'; alert(1); '"],
            ['red}</style>'],
            ['red\\3c script\\3e'],
        ];
    }
}
