<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
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
    use BaseDocumentNormalizerTrait;

    public function __construct(
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator,
        Stopwatch $stopwatch,
        private readonly FilesystemOperator $documentsStorage,
        private readonly EmbedFinderFactory $embedFinderFactory,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
    ) {
        parent::__construct($decorated, $urlGenerator, $stopwatch);
    }

    #[\Override]
    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $normalized = $this->decorated->normalize($data, $format, $context);

        if (
            !$data instanceof Document
            || !is_array($normalized)
        ) {
            return $normalized;
        }

        $this->stopwatch->start('normalizeDocument', 'serializer');
        /** @var array<string> $serializationGroups */
        $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

        if (
            $data->isProcessable()
            && null !== $alignment = $data->getImageCropAlignment()
        ) {
            $normalized['imageCropAlignment'] = $alignment;
        }

        if (
            \in_array('document_raw_relative_path', $serializationGroups, true)
            && !$data->isPrivate()
            && null !== $rawDocument = $data->getRawDocument()
        ) {
            $normalized['rawRelativePath'] = $rawDocument->getRelativePath();
        }

        if (
            \in_array('document_folders_all', $serializationGroups, true)
        ) {
            $normalized['folders'] = $data->getFolders()
                ->map(fn (FolderInterface $folder) => $this->decorated->normalize($folder, $format, $context))
                ->getValues()
            ;
        } elseif (
            \in_array('document_folders', $serializationGroups, true)
        ) {
            $normalized['folders'] = $data->getFolders()->filter(fn (FolderInterface $folder) => $folder->getVisible())->map(fn (FolderInterface $folder) => $this->decorated->normalize($folder, $format, $context))->getValues();
        }

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            /*
             * Always falls back on default translation if no translation is found for Documents entities
             */
            $translatedData = $data->getDocumentTranslationsByTranslation($context['translation'])->first() ?:
                $data->getDocumentTranslationsByDefaultTranslation();
            if ($translatedData instanceof DocumentTranslation) {
                $additionalData = [
                    'name' => $translatedData->getName() ?? null,
                    'description' => $translatedData->getDescription() ?? null,
                    'copyright' => $translatedData->getCopyright() ?? null,
                    'alt' => !empty($translatedData->getName()) ? $translatedData->getName() : null,
                    'externalUrl' => $translatedData->getExternalUrl(),
                ];
                $normalized = [
                    ...$normalized,
                    ...array_filter($additionalData),
                ];
            }
        }

        $this->appendToNormalizedData($data, $normalized, $serializationGroups);

        $this->stopwatch->stop('normalizeDocument');

        return $normalized;
    }
}
