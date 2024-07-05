<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\FolderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Override Document default normalization.
 */
final class DocumentNormalizer extends AbstractPathNormalizer
{
    private FilesystemOperator $documentsStorage;
    private EmbedFinderFactory $embedFinderFactory;

    public function __construct(
        FilesystemOperator $documentsStorage,
        NormalizerInterface $decorated,
        UrlGeneratorInterface $urlGenerator,
        EmbedFinderFactory $embedFinderFactory
    ) {
        parent::__construct($decorated, $urlGenerator);
        $this->documentsStorage = $documentsStorage;
        $this->embedFinderFactory = $embedFinderFactory;
    }

    /**
     * @param mixed $object
     * @param string|null $format
     * @param array $context
     * @return array|\ArrayObject|bool|float|int|string|null
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = $this->decorated->normalize($object, $format, $context);
        if (
            $object instanceof Document &&
            is_array($data)
        ) {
            /** @var array<string> $serializationGroups */
            $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];
            $data['type'] = $object->getShortType();

            if (
                !$object->isPrivate() &&
                !$object->isProcessable()
            ) {
                $mountPath = $object->getMountPath();
                if (null !== $mountPath) {
                    $data['publicUrl'] = $this->documentsStorage->publicUrl($mountPath);
                }
            }

            if (
                !$object->isPrivate() &&
                $object->isProcessable() &&
                null !== $alignment = $object->getImageCropAlignment()
            ) {
                $data['imageCropAlignment'] = $alignment;
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
                $object->getEmbedPlatform() &&
                $object->getEmbedId()
            ) {
                $embedFinder = $this->embedFinderFactory->createForPlatform(
                    $object->getEmbedPlatform(),
                    $object->getEmbedId()
                );
                if (null !== $embedFinder) {
                    $data['embedUrl'] = $embedFinder->getSource();
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
                    $data['alt'] = !empty($translatedData->getName()) ? $translatedData->getName() : $object->getFilename();
                    $data['externalUrl'] = $translatedData->getExternalUrl();
                }
            }
        }
        return $data;
    }
}
