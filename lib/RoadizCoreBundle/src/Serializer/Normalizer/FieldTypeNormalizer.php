<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class FieldTypeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [
            NodeTypeField::class => true,
        ];
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): int
    {
        return $object->value;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?FieldType
    {
        if (is_numeric($data)) {
            return FieldType::tryFrom((int) $data);
        }

        return null;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return null === $data || is_numeric($data);
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $data instanceof FieldType;
    }
}
