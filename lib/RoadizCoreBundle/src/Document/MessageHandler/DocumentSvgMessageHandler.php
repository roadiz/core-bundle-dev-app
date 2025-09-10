<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Document\MessageHandler;

use enshrined\svgSanitize\Sanitizer;
use RZ\Roadiz\CoreBundle\Document\Message\AbstractDocumentMessage;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\SizeableInterface;
use RZ\Roadiz\Documents\SvgSizeResolver;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

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

        // Create a new sanitizer instance
        $sanitizer = new Sanitizer();
        $sanitizer->minify(true);

        if (!$this->documentsStorage->fileExists($document->getMountPath())) {
            return;
        }

        // Load the dirty svg
        $dirtySVG = $this->documentsStorage->read($document->getMountPath());
        $cleanSVG = $sanitizer->sanitize($dirtySVG);

        if (false === $cleanSVG) {
            throw new UnrecoverableMessageHandlingException('SVG document could not be sanitized.');
        }

        $this->documentsStorage->write($document->getMountPath(), $cleanSVG);
        $this->messengerLogger->info('Svg document sanitized.');

        /*
         * Resolve SVG size
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
