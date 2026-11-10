<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Serializer\Enricher\NormalizationEnricherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Single decorator of API Platform item normalizers running every NormalizationEnricherInterface,
 * so the Serializer only goes through one layer when calling supportsNormalization().
 */
final readonly class EnrichingItemNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @param iterable<NormalizationEnricherInterface> $enrichers
     */
    public function __construct(
        private NormalizerInterface&DenormalizerInterface $decorated,
        private iterable $enrichers,
        private Stopwatch $stopwatch,
    ) {
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if (!\is_object($data) || !\is_array($normalized)) {
            return $normalized;
        }

        foreach ($this->enrichers as $enricher) {
            if ($enricher->supports($data, $format, $context)) {
                $this->stopwatch->start($enricher::class, 'serializer');
                $normalized = $enricher->enrich($normalized, $data, $format, $context);
                $this->stopwatch->stop($enricher::class);
            }
        }

        return $normalized;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsNormalization($data, $format, $context);
    }

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        return $this->decorated->denormalize($data, $type, $format, $context);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $this->decorated->supportsDenormalization($data, $type, $format, $context);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return $this->decorated->getSupportedTypes($format);
    }

    #[\Override]
    public function setSerializer(SerializerInterface $serializer): void
    {
        if ($this->decorated instanceof SerializerAwareInterface) {
            $this->decorated->setSerializer($serializer);
        }
        if (!$serializer instanceof NormalizerInterface) {
            return;
        }
        foreach ($this->enrichers as $enricher) {
            if ($enricher instanceof NormalizerAwareInterface) {
                $enricher->setNormalizer($serializer);
            }
        }
    }
}
