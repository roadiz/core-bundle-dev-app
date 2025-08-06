<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;

class ImageRenderer extends AbstractImageRenderer
{
    #[\Override]
    public function supports(BaseDocumentInterface $document, array $options): bool
    {
        return (!isset($options['picture']) || false === $options['picture'])
            && parent::supports($document, $options);
    }

    #[\Override]
    public function render(BaseDocumentInterface $document, array $options): string
    {
        $options = $this->viewOptionsResolver->resolve($options);

        /*
         * Override image by its first thumbnail if existing
         */
        if (
            !$options['no_thumbnail']
            && $document instanceof HasThumbnailInterface
            && $thumbnail = $document->getThumbnails()->first()
        ) {
            if ($thumbnail instanceof DocumentInterface) {
                $document = $thumbnail;
            }
        }

        $assignation = array_merge(
            array_filter($options),
            [
                'mimetype' => $document->getMimeType(),
                'url' => $this->getSource($document, $options),
                'media' => null,
            ]
        );
        $assignation['alt'] = !empty($options['alt']) ? $options['alt'] : $document->getAlternativeText();
        $assignation['sizes'] = $this->parseSizes($options);
        $assignation['srcset'] = $this->parseSrcSet($document, $options);

        if (
            null === $assignation['sizes']
            && $document instanceof AdvancedDocumentInterface
            && $document->getImageWidth() > 0
            && $document->getImageHeight() > 0
            && !$this->willResample($assignation)
        ) {
            $assignation['width'] = $document->getImageWidth();
            $assignation['height'] = $document->getImageHeight();
        }

        $this->additionalAssignation($document, $options, $assignation);

        return $this->renderHtmlElement('image.html.twig', $assignation);
    }
}
