<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Enricher;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Serializer\Normalizer\BaseDocumentNormalizerTrait;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\FolderInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

/**
 * Override Document default normalization.
 */
#[AsTaggedItem(priority: 20)]
final class DocumentEnricher implements NormalizationEnricherInterface, NormalizerAwareInterface
{
    use BaseDocumentNormalizerTrait;
    use NormalizerAwareTrait;

    public function __construct(
        private readonly FilesystemOperator $documentsStorage,
        private readonly EmbedFinderFactory $embedFinderFactory,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
    ) {
    }

    #[\Override]
    public function supports(object $data, ?string $format, array $context): bool
    {
        return $data instanceof Document;
    }

    /**
     * @param Document $data
     */
    #[\Override]
    public function enrich(array $normalized, object $data, ?string $format, array $context): array
    {
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
                ->map(fn (FolderInterface $folder) => $this->normalizer->normalize($folder, $format, $context))
                ->getValues()
            ;
        } elseif (
            \in_array('document_folders', $serializationGroups, true)
        ) {
            $normalized['folders'] = $data->getFolders()->filter(fn (FolderInterface $folder) => $folder->getVisible())->map(fn (FolderInterface $folder) => $this->normalizer->normalize($folder, $format, $context))->getValues();
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

        return $normalized;
    }
}
