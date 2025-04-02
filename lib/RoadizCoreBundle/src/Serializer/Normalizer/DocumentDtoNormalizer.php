<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use Doctrine\ORM\NonUniqueResultException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\CoreBundle\Model\DocumentDto;
use RZ\Roadiz\CoreBundle\Repository\DocumentRepository;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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

    public function getSupportedTypes(?string $format): array
    {
        return [
            DocumentDto::class => false,
        ];
    }

    /**
     * @return array|\ArrayObject|bool|float|int|string|null
     *
     * @throws ExceptionInterface
     * @throws NonUniqueResultException
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        /** @var array<string> $serializationGroups */
        $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
        $data = $this->normalizer->normalize($object, $format, $context);

        if (!\is_array($data)) {
            return $data;
        }

        $this->stopwatch->start('normalizeDocumentDto', 'serializer');

        if (\in_array('document_folders_all', $serializationGroups, true)
            || \in_array('document_folders', $serializationGroups, true)) {
            $document = $this->repository->findOneBy(['id' => $object->getId()]);

            if (\in_array('document_folders_all', $serializationGroups, true)) {
                $data['folders'] = $document->getFolders()
                    ->map(function (FolderInterface $folder) use ($format, $context) {
                        return $this->normalizer->normalize($folder, $format, $context);
                    })->getValues();
            } else {
                $data['folders'] = $document->getFolders()->filter(function (FolderInterface $folder) {
                    return $folder->getVisible();
                })->map(function (FolderInterface $folder) use ($format, $context) {
                    return $this->normalizer->normalize($folder, $format, $context);
                })->getValues();
            }
        }

        if (
            \in_array('document_thumbnails', $serializationGroups, true)
            && ($object->isEmbed() || !$object->isImage())
        ) {
            $thumbnail = $this->repository->findFirstThumbnailDtoBy($object->getId());
            if (null !== $thumbnail) {
                $data['thumbnail'] = $this->normalize(
                    $thumbnail,
                    $format,
                    [
                        ...$context,
                        'groups' => array_diff($serializationGroups, ['document_thumbnails']),
                    ],
                );
            }
        }

        $this->appendToNormalizedData($object, $data, $serializationGroups);

        $this->stopwatch->stop('normalizeDocumentDto');

        return $data;
    }

    public function supportsNormalization(mixed $data, ?string $format = null): bool
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }
}
