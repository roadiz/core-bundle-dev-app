<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Override Document default normalization.
 */
final class DocumentNormalizer extends AbstractPathNormalizer
{
    public function __construct(
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator,
        Stopwatch $stopwatch,
        private readonly FilesystemOperator $documentsStorage,
        private readonly EmbedFinderFactory $embedFinderFactory,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct($decorated, $urlGenerator, $stopwatch);
    }

    /**
     * @return array|\ArrayObject|bool|float|int|string|null
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): mixed
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (
            $object instanceof Document
            && is_array($data)
        ) {
            $this->stopwatch->start('normalizeDocument', 'serializer');
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
            $data['type'] = $object->getShortType();

            if (
                !$object->isPrivate()
                && !$object->isProcessable()
            ) {
                $mountPath = $object->getMountPath();
                if (null !== $mountPath) {
                    $data['publicUrl'] = $this->documentsStorage->publicUrl($mountPath);
                }
            }

            if (
                !$object->isPrivate()
                && $object->isProcessable()
                && null !== $alignment = $object->getImageCropAlignment()
            ) {
                $data['imageCropAlignment'] = $alignment;
            }

            if (
                \in_array('document_raw_relative_path', $serializationGroups, true)
                && !$object->isPrivate()
                && null !== $rawDocument = $object->getRawDocument()
            ) {
                $data['rawRelativePath'] = $rawDocument->getRelativePath();
            }

            if (
                \in_array('document_folders_all', $serializationGroups, true)
            ) {
                $data['folders'] = $object->getFolders()
                    ->map(function (FolderInterface $folder) use ($format, $context) {
                        return $this->decorated->normalize($folder, $format, $context);
                    })
                    ->getValues()
                ;
            } elseif (
                \in_array('document_folders', $serializationGroups, true)
            ) {
                $data['folders'] = $object->getFolders()->filter(function (FolderInterface $folder) {
                    return $folder->getVisible();
                })->map(function (FolderInterface $folder) use ($format, $context) {
                    return $this->decorated->normalize($folder, $format, $context);
                })->getValues();
            }

            if (
                $object->getEmbedPlatform()
                && $object->getEmbedId()
            ) {
                try {
                    $embedFinder = $this->embedFinderFactory->createForPlatform(
                        $object->getEmbedPlatform(),
                        $object->getEmbedId()
                    );
                    if (null !== $embedFinder) {
                        $data['embedUrl'] = $embedFinder->getSource();
                    }
                } catch (InvalidEmbedId $exception) {
                    $this->logger->error($exception->getMessage(), [
                        'document' => $object->getId(),
                        'embedId' => $object->getEmbedId(),
                        'embedPlatform' => $object->getEmbedPlatform(),
                    ]);
                }
            }

            if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
                /*
                 * Always falls back on default translation if no translation is found for Documents entities
                 */
                $translatedData = $object->getDocumentTranslationsByTranslation($context['translation'])->first() ?:
                    $object->getDocumentTranslationsByDefaultTranslation();
                if ($translatedData instanceof DocumentTranslation) {
                    $data['name'] = $translatedData->getName();
                    $data['description'] = $translatedData->getDescription();
                    $data['copyright'] = $translatedData->getCopyright();
                    $data['alt'] = !empty($translatedData->getName()) ? $translatedData->getName() : null;
                    $data['externalUrl'] = $translatedData->getExternalUrl();
                }
            }

            if (
                !$object->isPrivate()
                && \in_array('explorer_thumbnail', $serializationGroups, true)
            ) {
                $data['url'] = $this->documentUrlGenerator
                    ->setDocument($object)
                    ->setOptions([
                        'fit' => '250x200',
                        'quality' => 60,
                    ])
                    ->getUrl();
            }

            $this->stopwatch->stop('normalizeDocument');
        }

        return $data;
    }
}
