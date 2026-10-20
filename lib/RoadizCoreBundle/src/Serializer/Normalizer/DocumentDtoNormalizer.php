<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Documents\AbstractDocumentFinder;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

final readonly class DocumentDtoNormalizer implements NormalizerInterface
{
    use BaseDocumentNormalizerTrait;

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private DocumentRepository $repository,
        private EmbedFinderFactory $embedFinderFactory,
        private FilesystemOperator $documentsStorage,
        private Stopwatch $stopwatch,
        private DocumentUrlGeneratorInterface $documentUrlGenerator,
    ) {
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            DocumentDto::class => false,
        ];
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        /** @var array<string> $serializationGroups */
        $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
        $normalized = $this->normalizer->normalize($data, $format, $context);

        if (!\is_array($normalized)) {
            return $normalized;
        }

        $this->stopwatch->start('normalizeDocumentDto', 'serializer');

        /*
         * If no @id is set, we set it to /api/documents/{dtoId} to ensure compatibility with frontend and API consumers.
         */
        if (!array_key_exists('@id', $normalized) && null !== $data->getId()) {
            $normalized = [
                '@id' => '/api/documents/'.$data->getId(),
                ...$normalized,
            ];
        }
        /*
         * If no @type is set, we set it to Document to ensure compatibility with frontend and API consumers.
         */
        if (!array_key_exists('@type', $normalized)) {
            $normalized = [
                '@type' => 'Document',
                ...$normalized,
            ];
        }

        if (\in_array('document_folders_all', $serializationGroups, true)
            || \in_array('document_folders', $serializationGroups, true)) {
            $document = $this->repository->find($data->getId());
            if (null === $document) {
                throw new \RuntimeException('Document not found.');
            }

            if (\in_array('document_folders_all', $serializationGroups, true)) {
                $normalized['folders'] = $document->getFolders()
                    ->map(fn (FolderInterface $folder) => $this->normalizer->normalize($folder, $format, $context))->getValues();
            } else {
                $normalized['folders'] = $document->getFolders()->filter(fn (FolderInterface $folder) => $folder->getVisible())->map(fn (FolderInterface $folder) => $this->normalizer->normalize($folder, $format, $context))->getValues();
            }
        }

        if (
            \in_array('document_thumbnails', $serializationGroups, true)
            && ($data->isEmbed() || !$data->isImage())
        ) {
            $thumbnail = $this->repository->findFirstThumbnailDtoBy($data->getId());
            if (null !== $thumbnail) {
                $normalized['thumbnail'] = $this->normalize(
                    $thumbnail,
                    $format,
                    [
                        ...$context,
                        'groups' => array_diff($serializationGroups, ['document_thumbnails']),
                    ],
                );
            }
        }

        $this->appendToNormalizedData($data, $normalized, $serializationGroups);

        if (\in_array('document_display_sources', $serializationGroups, true)) {
            $altSources = $this->normalizeAltSources($data, $format, $context);
            if (null !== $altSources) {
                $normalized['altSources'] = $altSources;
            }
        }

        $this->stopwatch->stop('normalizeDocumentDto');

        return $normalized;
    }

    /**
     * Same output as DocumentSourcesEnricher, but alternative sources are fetched as DTOs too.
     *
     * @return array<mixed>|null null if document cannot have alternative sources
     */
    private function normalizeAltSources(DocumentDto $data, ?string $format, array $context): ?array
    {
        if (!$data->isLocal()) {
            return null;
        }
        $extensions = match (true) {
            $data->isVideo() => AbstractDocumentFinder::VIDEO_SOURCE_EXTENSIONS,
            $data->isAudio() => AbstractDocumentFinder::AUDIO_SOURCE_EXTENSIONS,
            default => null,
        };
        if (null === $extensions) {
            return null;
        }

        $relativePath = $data->getRelativePath();
        $basename = pathinfo($data->getFilename(), \PATHINFO_FILENAME);
        $sourceFilenames = array_map(fn (string $extension) => $basename.'.'.$extension, $extensions);
        $altSources = [];
        foreach ($this->repository->findDtoByFilenames($sourceFilenames) as $source) {
            // Some extensions (ogg) are shared between audio and video files.
            if ($source->getRelativePath() !== $relativePath && $source->getShortType() === $data->getShortType()) {
                $altSources[] = $this->normalize($source, $format, [...$context, 'groups' => ['document_source']]);
            }
        }

        return $altSources;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format, $context);
    }
}
