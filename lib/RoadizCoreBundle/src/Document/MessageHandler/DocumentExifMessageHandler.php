<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentExifMessage;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Entity\DocumentTranslation;
use RZ\Roadiz\CoreBundle\Entity\Translation;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler(handles: DocumentExifMessage::class)]
final class DocumentExifMessageHandler extends AbstractDocumentMessageHandler
{
    #[\Override]
    protected function supports(DocumentInterface $document): bool
    {
        if (!$document->isLocal()) {
            return false;
        }

        if ('' !== $document->getEmbedPlatform()) {
            return false;
        }

        if ('image/jpeg' === $document->getMimeType() || 'image/tiff' === $document->getMimeType()) {
            return true;
        }

        return false;
    }

    #[\Override]
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        $mountPath = $document->getMountPath();

        if (null === $mountPath) {
            return;
        }

        if (!$document instanceof Document) {
            return;
        }
        if (
            !function_exists('exif_read_data')
            || $document->getDocumentTranslations()->count() > 0
        ) {
            return;
        }

        $fileStream = $this->documentsStorage->readStream($mountPath);
        $exif = @\exif_read_data($fileStream, 'FILE,COMPUTED,ANY_TAG,EXIF,COMMENT');

        if (false === $exif) {
            return;
        }

        $copyright = $this->getCopyright($exif);
        $description = $this->getDescription($exif);

        if (null === $copyright && null === $description) {
            return;
        }

        $this->messengerLogger->debug(
            'EXIF information available for document.',
            [
                'document' => (string) $document,
            ]
        );
        $manager = $this->managerRegistry
            ->getManagerForClass(DocumentTranslation::class) ?? throw new UnrecoverableMessageHandlingException('No manager found for DocumentTranslation entity.');
        $defaultTranslation = $this->managerRegistry
            ->getRepository(Translation::class)
            ->findDefault() ?? throw new UnrecoverableMessageHandlingException('No default translation found.');

        $documentTranslation = new DocumentTranslation();
        $documentTranslation->setCopyright($copyright)
            ->setDocument($document)
            ->setDescription($description)
            ->setTranslation($defaultTranslation);

        $manager->persist($documentTranslation);
    }

    private function getCopyright(array $exif): ?string
    {
        foreach ($exif as $key => $section) {
            if (is_array($section)) {
                foreach ($section as $skey => $value) {
                    if ('copyright' === \mb_strtolower((string) $skey)) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }

    private function getDescription(array $exif): ?string
    {
        foreach ($exif as $key => $section) {
            if (is_string($section) && 'imagedescription' === \mb_strtolower((string) $key)) {
                return $section;
            } elseif (is_array($section)) {
                if ('comment' == \mb_strtolower((string) $key)) {
                    $comment = '';
                    foreach ($section as $value) {
                        $comment .= $value.PHP_EOL;
                    }

                    return $comment;
                }
                foreach ($section as $skey => $value) {
                    if ('comment' == \mb_strtolower((string) $skey)) {
                        return $value;
                    }
                }
            }
        }

        return null;
    }
}
