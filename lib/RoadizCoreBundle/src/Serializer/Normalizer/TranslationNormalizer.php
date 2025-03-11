<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\Persistence\ManagerRegistry;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final readonly class TranslationNormalizer implements DenormalizerInterface
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Translation::class => true,
        ];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Translation
    {
        $locale = $data['locale'];

        if (!is_string($locale)) {
            throw new \InvalidArgumentException('Translation locale name must be a string.');
        }

        $translation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findOneByLocale($locale);

        if (null === $translation) {
            throw new NotNormalizableValueException('Translation for locale "'.$locale.'" not found.');
        }

        return $translation;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null): bool
    {
        return is_array($data) && array_key_exists('locale', $data);
    }
}
