<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Api\TreeWalker;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\AutoChildrenNodeSourceWalker;
use RZ\Roadiz\CoreBundle\Api\TreeWalker\WalkerChildrenPropertyMetadataFactory;
use Symfony\Component\TypeInfo\Type;

/**
 * Walker children must serialize without random skolem IRIs, whatever the walker class.
 */
final class WalkerChildrenPropertyMetadataFactoryTest extends TestCase
{
    public function testWalkerChildrenAreTypedAndWithoutGeneratedId(): void
    {
        $metadata = $this->createFactory()->create(AutoChildrenNodeSourceWalker::class, 'children');

        $this->assertFalse($metadata->getGenId());
        $this->assertEquals(
            Type::collection(Type::object(Collection::class), Type::object(AutoChildrenNodeSourceWalker::class), Type::int()),
            $metadata->getNativeType()
        );
    }

    public function testOtherPropertiesAndClassesAreUntouched(): void
    {
        $factory = $this->createFactory();

        $this->assertEquals(new ApiProperty(), $factory->create(AutoChildrenNodeSourceWalker::class, 'metadata'));
        $this->assertEquals(new ApiProperty(), $factory->create(\stdClass::class, 'children'));
    }

    private function createFactory(): WalkerChildrenPropertyMetadataFactory
    {
        return new WalkerChildrenPropertyMetadataFactory(new class implements PropertyMetadataFactoryInterface {
            public function create(string $resourceClass, string $property, array $options = []): ApiProperty
            {
                return new ApiProperty();
            }
        });
    }
}
