<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Serialize document sources.
 */
#[AsTaggedItem(priority: 19)]
final class DocumentSourcesEnricher implements NormalizationEnricherInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(
        private readonly DocumentFinderInterface $documentFinder,
    ) {
    }

    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof Document
            && null !== $data->getRelativePath()
            && isset($context['groups'])
            && is_array($context['groups'])
            && \in_array('document_display_sources', $context['groups'], true);
    }

    /**
     * @param Document $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
        $relativePath = (string) $data->getRelativePath();
        /*
         * Reduce serialization group to avoid normalization loop.
         */
        $sourcesContext = $context;
        $sourcesContext['groups'] = ['document_source'];
        if ($data->isLocal() && $data->isVideo()) {
            $normalized['altSources'] = [];
            foreach ($this->documentFinder->findVideosWithFilename($relativePath) as $document) {
                if ($document->getRelativePath() !== $relativePath) {
                    $normalized['altSources'][] = $this->normalizer->normalize($document, $format, $sourcesContext);
                }
            }
        } elseif ($data->isLocal() && $data->isAudio()) {
            $normalized['altSources'] = [];
            foreach ($this->documentFinder->findAudiosWithFilename($relativePath) as $document) {
                if ($document->getRelativePath() !== $relativePath) {
                    $normalized['altSources'][] = $this->normalizer->normalize($document, $format, $sourcesContext);
                }
            }
        }

        return $normalized;
    }
}
