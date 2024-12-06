<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class SvgSizeResolver
{
    private ?\DOMDocument $xmlDocument = null;
    private ?\DOMNode $svgNode = null;

    public function __construct(
        private readonly DocumentInterface $document,
        private readonly FilesystemOperator $documentsStorage,
    ) {
    }

    /**
     * @return array|null [$x, $y, $width, $height]
     */
    protected function getViewBoxAttributes(): ?array
    {
        try {
            $viewBox = $this->getSvgNodeAttributes()->getNamedItem('viewBox');
            if (null !== $viewBox && '' !== $viewBox->textContent) {
                return explode(' ', $viewBox->textContent);
            }
        } catch (\RuntimeException $exception) {
            return null;
        }

        return null;
    }

    protected function getIntegerAttribute(string $name): ?int
    {
        try {
            $attribute = $this->getSvgNodeAttributes()->getNamedItem($name);
            if (
                null !== $attribute
                && '' !== $attribute->textContent
                && !\str_contains($attribute->textContent, '%')
            ) {
                return (int) $attribute->textContent;
            }
        } catch (\RuntimeException $exception) {
            return null;
        }

        return null;
    }

    /**
     * First, find width attr, then resolve width from viewBox.
     */
    public function getWidth(): int
    {
        $widthAttr = $this->getIntegerAttribute('width');
        if (null !== $widthAttr) {
            return $widthAttr;
        }

        $viewBoxAttr = $this->getViewBoxAttributes();
        if (null !== $viewBoxAttr) {
            [$x, $y, $width, $height] = $viewBoxAttr;

            return (int) $width;
        }

        return 0;
    }

    /**
     * First, find height attr, then resolve height from viewBox.
     */
    public function getHeight(): int
    {
        $heightAttr = $this->getIntegerAttribute('height');
        if (null !== $heightAttr) {
            return $heightAttr;
        }
        $viewBoxAttr = $this->getViewBoxAttributes();
        if (null !== $viewBoxAttr) {
            [$x, $y, $width, $height] = $viewBoxAttr;

            return (int) $height;
        }

        return 0;
    }

    private function getSvgNode(): \DOMElement
    {
        if (null === $this->svgNode) {
            $svg = $this->getDOMDocument()->getElementsByTagName('svg');
            if (!isset($svg[0])) {
                throw new \RuntimeException('SVG does not contain a valid <svg> tag');
            }
            $this->svgNode = $svg[0];
        }

        return $this->svgNode;
    }

    private function getSvgNodeAttributes(): \DOMNamedNodeMap
    {
        /** @var \DOMNamedNodeMap|null $attributes */
        $attributes = $this->getSvgNode()->attributes;
        if (null === $attributes) {
            throw new \RuntimeException('SVG tag <svg> does not contain any attribute');
        }

        return $attributes;
    }

    /**
     * @throws FilesystemException
     */
    private function getDOMDocument(): \DOMDocument
    {
        if (null === $this->xmlDocument) {
            $mountPath = $this->document->getMountPath();
            if (null === $mountPath) {
                throw new \RuntimeException('SVG does not have file.');
            }
            $this->xmlDocument = new \DOMDocument();
            $svgSource = $this->documentsStorage->read($mountPath);
            if (false === $this->xmlDocument->loadXML($svgSource)) {
                throw new \RuntimeException(sprintf('SVG (%s) could not be loaded.', $mountPath));
            }
        }

        return $this->xmlDocument;
    }
}
