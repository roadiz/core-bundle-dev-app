<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DocumentSourcesNormalizer extends AbstractPathNormalizer
{
    protected DocumentFinderInterface $documentFinder;

    public function __construct(
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator,
        DocumentFinderInterface $documentFinder
    ) {
        parent::__construct($decorated, $urlGenerator);
        $this->documentFinder = $documentFinder;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|mixed|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if ($object instanceof Document && is_array($data)) {
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

            if (\in_array('document_display_sources', $serializationGroups, true)) {
                /*
                 * Reduce serialization group to avoid normalization loop.
                 */
                $sourcesContext = $context;
                $sourcesContext['groups'] = ['document_display'];

                if ($object->isLocal() && $object->isVideo()) {
                    $data['altSources'] = [];
                    foreach ($this->documentFinder->findVideosWithFilename($object->getRelativePath()) as $document) {
                        if ($document->getRelativePath() !== $object->getRelativePath()) {
                            $data['altSources'][] = $this->decorated->normalize($document, $format, $sourcesContext);
                        }
                    }
                } elseif ($object->isLocal() && $object->isAudio()) {
                    $data['altSources'] = [];
                    foreach ($this->documentFinder->findAudiosWithFilename($object->getRelativePath()) as $document) {
                        if ($document->getRelativePath() !== $object->getRelativePath()) {
                            $data['altSources'][] = $this->decorated->normalize($document, $format, $sourcesContext);
                        }
                    }
                }
            }
        }
        return $data;
    }
}
