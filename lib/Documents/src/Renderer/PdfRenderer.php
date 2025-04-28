<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

class PdfRenderer extends AbstractRenderer
{
    public function supports(BaseDocumentInterface $document, array $options): bool
    {
        return $document->isPdf()
            && key_exists('embed', $options)
            && true === $options['embed'];
    }

    /**
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\LoaderError
     */
    public function render(BaseDocumentInterface $document, array $options): string
    {
        $options = $this->viewOptionsResolver->resolve($options);

        $assignation = array_merge(
            array_filter($options),
            [
                'url' => $this->getSource($document, $options),
            ]
        );

        return $this->renderHtmlElement('pdf.html.twig', $assignation);
    }
}
