<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final class DocumentSourcesNormalizer extends AbstractPathNormalizer
{
    public function __construct(
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator,
        Stopwatch $stopwatch,
        private readonly DocumentFinderInterface $documentFinder,
    ) {
        parent::__construct($decorated, $urlGenerator, $stopwatch);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);
        if ($data instanceof Document && is_array($normalized)) {
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

            if (\in_array('document_display_sources', $serializationGroups, true)) {
                /*
                 * Reduce serialization group to avoid normalization loop.
                 */
                $sourcesContext = $context;
                $sourcesContext['groups'] = ['document_display'];

                if ($data->isLocal() && $data->isVideo()) {
                    $normalized['altSources'] = [];
                    foreach ($this->documentFinder->findVideosWithFilename($data->getRelativePath()) as $document) {
                        if ($document->getRelativePath() !== $data->getRelativePath()) {
                            $normalized['altSources'][] = $this->decorated->normalize($document, $format, $sourcesContext);
                        }
                    }
                } elseif ($data->isLocal() && $data->isAudio()) {
                    $normalized['altSources'] = [];
                    foreach ($this->documentFinder->findAudiosWithFilename($data->getRelativePath()) as $document) {
                        if ($document->getRelativePath() !== $data->getRelativePath()) {
                            $normalized['altSources'][] = $this->decorated->normalize($document, $format, $sourcesContext);
                        }
                    }
                }
            }
        }

        return $normalized;
    }
}
