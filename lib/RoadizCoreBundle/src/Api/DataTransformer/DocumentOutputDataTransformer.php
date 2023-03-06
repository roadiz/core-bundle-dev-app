<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Api\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use RZ\Roadiz\Core\AbstractEntities\TranslationInterface;
use RZ\Roadiz\CoreBundle\Api\Dto\DocumentOutput;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;

/**
 * @deprecated Use DocumentNormalizer
 */
class DocumentOutputDataTransformer implements DataTransformerInterface
{
    protected DocumentFinderInterface $documentFinder;

    public function __construct(DocumentFinderInterface $documentFinder)
    {
        $this->documentFinder = $documentFinder;
    }

    /**
     * @inheritDoc
     */
    public function transform($object, string $to, array $context = []): object
    {
        if (!$object instanceof Document) {
            throw new \InvalidArgumentException('Data to transform must be instance of ' . DocumentInterface::class);
        }
        $output = new DocumentOutput();
        $output->relativePath = $object->getRelativePath();
        $output->processable = $object->isProcessable();
        $output->type = $object->getShortType();
        $output->imageWidth = $object->getImageWidth();
        $output->imageHeight = $object->getImageHeight();
        $output->mimeType = $object->getMimeType();
        $output->alt = $object->getFilename();
        $output->embedId = $object->getEmbedId();
        $output->embedPlatform = $object->getEmbedPlatform();
        $output->imageAverageColor = $object->getImageAverageColor();
        $output->mediaDuration = $object->getMediaDuration();

        /** @var array<string> $serializationGroups */
        $serializationGroups = isset($context['groups']) && is_array($context['groups']) ? $context['groups'] : [];

        if (($object->isEmbed() || !$object->isImage()) && false !== $object->getThumbnails()->first()) {
            $output->thumbnail = $object->getThumbnails()->first();
        }

        if (isset($context['translation']) && $context['translation'] instanceof TranslationInterface) {
            $translatedData = $object->getDocumentTranslationsByTranslation($context['translation'])->first() ?: null;
            if ($translatedData instanceof DocumentTranslation) {
                $output->name = $translatedData->getName();
                $output->description = $translatedData->getDescription();
                $output->copyright = $translatedData->getCopyright();
                $output->alt = !empty($translatedData->getName()) ? $translatedData->getName() : $object->getFilename();
                $output->externalUrl = $translatedData->getExternalUrl();
            }
        }

        if (in_array('document_folders', $serializationGroups)) {
            $output->folders = $object->getFolders()->toArray();
        }

        if (in_array('document_display_sources', $serializationGroups)) {
            if ($object->isLocal() && $object->isVideo()) {
                foreach ($this->documentFinder->findVideosWithFilename($object->getRelativePath()) as $document) {
                    if ($document->getRelativePath() !== $object->getRelativePath()) {
                        $output->altSources[] = $document;
                    }
                }
            } elseif ($object->isLocal() && $object->isAudio()) {
                foreach ($this->documentFinder->findAudiosWithFilename($object->getRelativePath()) as $document) {
                    if ($document->getRelativePath() !== $object->getRelativePath()) {
                        $output->altSources[] = $document;
                    }
                }
            }
        }

        return $output;
    }

    /**
     * @inheritDoc
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        return DocumentOutput::class === $to && $data instanceof DocumentInterface;
    }
}
