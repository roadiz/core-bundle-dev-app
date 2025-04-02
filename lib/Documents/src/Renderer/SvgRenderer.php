<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\OptionsResolver\ViewOptionsResolver;
use RZ\Roadiz\Documents\Viewers\SvgDocumentViewer;

class SvgRenderer implements RendererInterface
{
    protected ViewOptionsResolver $viewOptionsResolver;

    public function __construct(protected readonly FilesystemOperator $documentsStorage)
    {
        $this->viewOptionsResolver = new ViewOptionsResolver();
    }

    public function supports(DocumentInterface $document, array $options): bool
    {
        return $document->isSvg() && (!isset($options['inline']) || false === $options['inline']);
    }

    public function render(DocumentInterface $document, array $options): string
    {
        $mountPath = $document->getMountPath();
        if (null === $mountPath) {
            return '';
        }
        $options = $this->viewOptionsResolver->resolve($options);
        $assignation = array_filter($options);
        $attributes = $this->getAttributes($assignation);
        $attributes['src'] = $this->documentsStorage->publicUrl($mountPath);

        $attrs = [];
        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                $value = htmlspecialchars($value);
            }
            $attrs[] = $key.'="'.$value.'"';
        }

        return '<img '.implode(' ', $attrs).' />';
    }

    protected function getAttributes(array $options): array
    {
        $attributes = [];
        $allowedAttributes = array_merge(
            SvgDocumentViewer::$allowedAttributes,
            [
                'loading',
                'alt',
            ]
        );
        foreach ($options as $key => $value) {
            if (in_array($key, $allowedAttributes)) {
                if ('identifier' === $key) {
                    $attributes['id'] = $value;
                } else {
                    $attributes[$key] = $value;
                }
            }
        }

        return $attributes;
    }
}
