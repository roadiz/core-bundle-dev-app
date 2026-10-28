<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Form\Constraint;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Form\Constraint\HexadecimalColor;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class HexadecimalColorTest extends TestCase
{
    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidator();
    }

    /**
     * @dataProvider provideValidColors
     */
    public function testValidColorsArePermitted(string $color): void
    {
        $this->assertCount(0, $this->getValidator()->validate($color, new HexadecimalColor()));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideValidColors(): iterable
    {
        yield ['#ff0000'];
        yield ['#FF0000'];
        yield ['#000000'];
        yield ['#AbCdEf'];
    }

    /**
     * @dataProvider provideInvalidColors
     */
    public function testInvalidColorsAreRejected(string $color): void
    {
        $this->assertCount(1, $this->getValidator()->validate($color, new HexadecimalColor()));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideInvalidColors(): iterable
    {
        // Unanchored regex would have let a valid hex color hide inside a larger string.
        yield ['xxx#ff0000xxx'];
        // Wrong length.
        yield ['#ff00'];
        yield ['#ff00000'];
        // Not hexadecimal.
        yield ['#gggggg'];
        yield ['red'];
    }
}
