<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Serializer\Normalizer;

use RZ\Roadiz\Documents\Exceptions\InvalidEmbedId;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

trait BaseDocumentNormalizerTrait
{
    protected function appendToNormalizedData(BaseDocumentInterface $object, array &$data, array $serializationGroups = []): void
    {
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
            } catch (InvalidEmbedId) {
            }
        }

        /*
         * Adds publicUrl to document if it is not private and not processable. I.e. a PDF file.
         */
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
            $object->isProcessable()
            && \in_array('explorer_thumbnail', $serializationGroups, true)
        ) {
            $data['url'] = $this->documentUrlGenerator
                ->setDocument($object)
                ->setOptions([
                    'width' => 250,
                    'crop' => '5:4',
                    'quality' => 60,
                    'sharpen' => 3,
                ])
                ->getUrl();
        }
    }
}
