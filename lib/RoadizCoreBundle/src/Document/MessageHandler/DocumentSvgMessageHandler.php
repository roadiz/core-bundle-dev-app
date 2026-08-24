<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\CoreBundle\Document\Message\DocumentSvgMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\SvgSizeResolver;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler(handles: DocumentSvgMessage::class, priority: -100)]
final class DocumentSvgMessageHandler extends AbstractLockingDocumentMessageHandler
{
    #[\Override]
    protected function supports(DocumentInterface $document): bool
    {
        return $document->isLocal() && null !== $document->getRelativePath() && $document->isSvg();
    }

    #[\Override]
    protected function processMessage(AbstractDocumentMessage $message, DocumentInterface $document): void
    {
        if (!$document instanceof SizeableInterface) {
            return;
        }

        $mountPath = $document->getMountPath();
        if (null === $mountPath) {
            throw new UnrecoverableMessageHandlingException('Document mount path is null.');
        }

        if (!$this->documentsStorage->fileExists($mountPath)) {
            return;
        }

        /*
         * SVG content is already sanitized synchronously by
         * AbstractDocumentFactory::sanitizeSvgFileIfNeeded() before storage,
         * so this handler only resolves the SVG size.
         */
        try {
            $svgSizeResolver = new SvgSizeResolver($document, $this->documentsStorage);
            $document->setImageWidth($svgSizeResolver->getWidth());
            $document->setImageHeight($svgSizeResolver->getHeight());
        } catch (\RuntimeException $exception) {
            $this->messengerLogger->error($exception->getMessage());
        }
    }
}
