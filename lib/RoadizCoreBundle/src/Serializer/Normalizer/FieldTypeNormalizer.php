<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\NodeTypeField;
use RZ\Roadiz\CoreBundle\Enum\FieldType;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class FieldTypeNormalizer implements NormalizerInterface, DenormalizerInterface
{
    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            NodeTypeField::class => true,
        ];
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): int
    {
        return $data->value;
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): ?FieldType
    {
        if (is_numeric($data)) {
            return FieldType::tryFrom((int) $data);
        }

        return null;
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return null === $data || is_numeric($data);
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof FieldType;
    }
}
