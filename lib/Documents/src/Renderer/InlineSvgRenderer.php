<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\OptionsResolver\ViewOptionsResolver;
use RZ\Roadiz\Documents\Viewers\SvgDocumentViewer;

class InlineSvgRenderer implements RendererInterface
{
    protected ViewOptionsResolver $viewOptionsResolver;
    protected FilesystemOperator $documentsStorage;

    public function __construct(FilesystemOperator $documentsStorage)
    {
        $this->viewOptionsResolver = new ViewOptionsResolver();
        $this->documentsStorage = $documentsStorage;
    }

    public function supports(DocumentInterface $document, array $options): bool
    {
        return $document->isLocal() && $document->isSvg() && (isset($options['inline']) && true === $options['inline']);
    }

    /**
     * @throws FilesystemException
     */
    public function render(DocumentInterface $document, array $options): string
    {
        $options = $this->viewOptionsResolver->resolve($options);
        $assignation = array_filter($options);

        try {
            $viewer = new SvgDocumentViewer(
                $this->documentsStorage,
                $document,
                $assignation
            );

            return trim($this->htmlTidy($viewer->getContent()));
        } catch (\Exception $e) {
            return '<p>'.$e->getMessage().'</p>';
        }
    }

    protected function htmlTidy(string $body): string
    {
        return preg_replace('#\>[\n\r\s]+\<#', '><', $body) ?? '';
    }
}
