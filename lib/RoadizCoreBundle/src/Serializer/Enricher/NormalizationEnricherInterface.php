<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Post-processes the array produced by API Platform item normalizers.
 *
 * Enrichers run in descending tag priority (use #[AsTaggedItem(priority: …)]).
 * Normalize nested objects through the Serializer (implement NormalizerAwareInterface),
 * never through the inner normalizer, so that every enricher and decorator applies to them.
 */
#[AutoconfigureTag(self::TAG)]
interface NormalizationEnricherInterface
{
    public const string TAG = 'roadiz_core.normalization_enricher';

    public function supports(object $data, ?string $format, array $context): bool;

    public function enrich(array $normalized, object $data, ?string $format, array $context): array;
}
