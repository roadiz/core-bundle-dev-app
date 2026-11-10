<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Tests\Serializer\Normalizer;

use PHPUnit\Framework\TestCase;
use RZ\Roadiz\CoreBundle\Serializer\Enricher\NormalizationEnricherInterface;
use RZ\Roadiz\CoreBundle\Serializer\Normalizer\EnrichingItemNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * The composite replaced one decorator per enrichment: it must keep their order,
 * and give enrichers the Serializer so nested objects go through the whole chain.
 */
final class EnrichingItemNormalizerTest extends TestCase
{
    public function testEnrichersRunInIterationOrderOnSupportedObjects(): void
    {
        $normalizer = new EnrichingItemNormalizer(
            $this->createInner(['id' => 1]),
            [
                $this->createEnricher(fn (array $n) => [...$n, 'trail' => ($n['trail'] ?? '').'a']),
                $this->createEnricher(fn (array $n) => [...$n, 'trail' => ($n['trail'] ?? '').'b'], false),
                $this->createEnricher(fn (array $n) => [...$n, 'trail' => ($n['trail'] ?? '').'c']),
            ],
            new Stopwatch(),
        );

        $this->assertSame(['id' => 1, 'trail' => 'ac'], $normalizer->normalize(new \stdClass(), 'jsonld'));
    }

    public function testNonArrayOutputIsNotEnriched(): void
    {
        $normalizer = new EnrichingItemNormalizer(
            $this->createInner('/api/things/1'),
            [$this->createEnricher(fn () => throw new \LogicException('Must not be called'))],
            new Stopwatch(),
        );

        $this->assertSame('/api/things/1', $normalizer->normalize(new \stdClass(), 'jsonld'));
    }

    public function testSerializerIsForwardedToNormalizerAwareEnrichers(): void
    {
        $enricher = new class implements NormalizationEnricherInterface, NormalizerAwareInterface {
            use NormalizerAwareTrait;

            public function getNormalizer(): NormalizerInterface
            {
                return $this->normalizer;
            }

            public function supports(object $data, ?string $format, array $context): bool
            {
                return false;
            }

            public function enrich(array $normalized, object $data, ?string $format, array $context): array
            {
                return $normalized;
            }
        };

        $serializer = new Serializer([
            new EnrichingItemNormalizer($this->createInner([]), [$enricher], new Stopwatch()),
        ]);

        $this->assertSame($serializer, $enricher->getNormalizer());
    }

    private function createInner(mixed $output): NormalizerInterface&DenormalizerInterface
    {
        return new readonly class($output) implements NormalizerInterface, DenormalizerInterface {
            public function __construct(private mixed $output)
            {
            }

            public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
            {
                return $this->output;
            }

            public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
            {
                return true;
            }

            public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
            {
                return null;
            }

            public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
            {
                return false;
            }

            public function getSupportedTypes(?string $format): array
            {
                return ['object' => false];
            }
        };
    }

    private function createEnricher(\Closure $enrich, bool $supports = true): NormalizationEnricherInterface
    {
        return new readonly class($enrich, $supports) implements NormalizationEnricherInterface {
            public function __construct(private \Closure $enrich, private bool $supports)
            {
            }

            public function supports(object $data, ?string $format, array $context): bool
            {
                return $this->supports;
            }

            public function enrich(array $normalized, object $data, ?string $format, array $context): array
            {
                return ($this->enrich)($normalized);
            }
        };
    }
}
