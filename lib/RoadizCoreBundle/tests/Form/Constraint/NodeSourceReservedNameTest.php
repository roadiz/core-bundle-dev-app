<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Form\Constraint;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Form\Constraint\NodeSourceReservedName;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class NodeSourceReservedNameTest extends TestCase
{
    private function getValidator(): ValidatorInterface
    {
        return Validation::createValidator();
    }

    /**
     * @dataProvider provideReservedNames
     */
    public function testReservedNamesAreRejected(string $name): void
    {
        $this->assertTrue(NodeSourceReservedName::isReserved($name));
        $this->assertCount(1, $this->getValidator()->validate($name, new NodeSourceReservedName()));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideReservedNames(): iterable
    {
        // Both camelCase and snake_case spellings collide with a base getter.
        yield ['title'];
        yield ['metaTitle'];
        yield ['meta_title'];
        yield ['metaDescription'];
        yield ['meta_description'];
        yield ['shareImage'];
        yield ['share_image'];
        yield ['publishedAt'];
        yield ['published_at'];
        yield ['noIndex'];
        yield ['node'];
        yield ['parent'];
        yield ['translation'];
    }

    /**
     * @dataProvider provideAllowedNames
     */
    public function testAllowedNamesArePermitted(string $name): void
    {
        $this->assertFalse(NodeSourceReservedName::isReserved($name));
        $this->assertCount(0, $this->getValidator()->validate($name, new NodeSourceReservedName()));
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideAllowedNames(): iterable
    {
        yield ['content'];
        yield ['images'];
        yield ['header_image'];
        yield ['over_title'];
        yield ['pictures'];
        yield [''];
    }
}
