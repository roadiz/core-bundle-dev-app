<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;

final class SvgSizeResolver
{
    private ?\DOMDocument $xmlDocument = null;
    private ?\DOMElement $svgNode = null;

    public function __construct(
        private readonly DocumentInterface $document,
        private readonly FilesystemOperator $documentsStorage,
    ) {
    }

    /**
     * @return array|null [$x, $y, $width, $height]
     */
    private function getViewBoxAttributes(): ?array
    {
        try {
            $viewBox = $this->getSvgNodeAttributes()->getNamedItem('viewBox');
            if (null !== $viewBox && '' !== $viewBox->textContent) {
                return explode(' ', $viewBox->textContent);
            }
        } catch (\RuntimeException) {
            return null;
        }

        return null;
    }

    private function getIntegerAttribute(string $name): ?int
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
        } catch (\RuntimeException) {
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
            $node = $svg->item(0);
            if (!$node instanceof \DOMElement) {
                throw new \RuntimeException('SVG does not contain a valid <svg> tag');
            }
            $this->svgNode = $node;
        }

        return $this->svgNode;
    }

    private function getSvgNodeAttributes(): \DOMNamedNodeMap
    {
        return $this->getSvgNode()->attributes;
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
            // LIBXML_NONET prevents network entity resolution; LIBXML_NOENT keeps
            // entities unexpanded (defense-in-depth against XXE on PHP < 8 and any
            // future regression — PHP 8 disables external entities by default but
            // explicit flags make the intent clear and version-independent).
            if (false === $this->xmlDocument->loadXML($svgSource, \LIBXML_NONET | \LIBXML_NOENT)) {
                throw new \RuntimeException(sprintf('SVG (%s) could not be loaded.', $mountPath));
            }
        }

        return $this->xmlDocument;
    }
}
