<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
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

        if (\in_array('document_folders_all', $serializationGroups, true)
            || \in_array('document_folders', $serializationGroups, true)) {
            $document = $this->repository->findOneBy(['id' => $data->getId()]);

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

        $this->stopwatch->stop('normalizeDocumentDto');

        return $normalized;
    }

    #[\Override]
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $this->normalizer->supportsNormalization($data, $format, $context);
    }
}
