<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\JsonType;

/**
 * Backward-compatible "array" type for DBAL 4 which removed the built-in ArrayType.
 * Reads both PHP-serialized and JSON data; always writes JSON.
 */
final class ArrayType extends JsonType
{
    public function getName(): string
    {
        return 'array';
    }

    #[\Override]
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_string($value) && !str_starts_with($value, '[') && !str_starts_with($value, '{')) {
            // Legacy PHP-serialized data
            $val = @\unserialize($value);
            if (false !== $val || 'b:0;' === $value) {
                return $val;
            }
        }

        return parent::convertToPHPValue($value, $platform);
    }
}
