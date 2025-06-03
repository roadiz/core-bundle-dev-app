<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;

class PictureRenderer extends AbstractImageRenderer
{
    #[\Override]
    public function supports(BaseDocumentInterface $document, array $options): bool
    {
        return isset($options['picture'])
            && true === $options['picture']
            && parent::supports($document, $options);
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
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
                'isWebp' => $document->isWebp(),
                'url' => $this->getSource($document, $options),
                'media' => null,
                'srcset' => null,
                'webp_srcset' => null,
                'mediaList' => null,
            ]
        );
        $assignation['alt'] = !empty($options['alt']) ? $options['alt'] : $document->getAlternativeText();
        $assignation['sizes'] = $this->parseSizes($options);

        if (count($options['media']) > 0) {
            $assignation['mediaList'] = $this->parseMedia($document, $options);
        } else {
            $assignation['srcset'] = $this->parseSrcSet($document, $options);
            if (!$document->isWebp()) {
                $assignation['webp_srcset'] = $this->parseSrcSet($document, $options, true);
            }
        }

        $this->additionalAssignation($document, $options, $assignation);

        return $this->renderHtmlElement('picture.html.twig', $assignation);
    }

    private function parseMedia(BaseDocumentInterface $document, array $options = []): array
    {
        $mediaList = [];
        foreach ($options['media'] as $media) {
            if (!isset($media['srcset'])) {
                throw new \InvalidArgumentException('Picture media list must have srcset option.');
            }
            if (!isset($media['rule'])) {
                throw new \InvalidArgumentException('Picture media list must have rule option.');
            }
            $mediaList[] = [
                'srcset' => $this->parseSrcSetInner($document, $media['srcset'], false, $options['absolute']),
                'webp_srcset' => !$document->isWebp() ? $this->parseSrcSetInner($document, $media['srcset'], true, $options['absolute']) : null,
                'rule' => $media['rule'],
            ];
        }

        return $mediaList;
    }
}
