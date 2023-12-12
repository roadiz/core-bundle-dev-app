<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Viewers;

use enshrined\svgSanitize\Sanitizer;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;

class SvgDocumentViewer
{
    protected array $attributes;
    protected bool $asObject = false;
    protected string $imageUrl;
    protected FilesystemOperator $documentsStorage;
    protected DocumentInterface $document;

    /**
     * @var string[]
     */
    public static array $allowedAttributes = [
        'width',
        'height',
        'identifier',
        'class',
    ];

    /**
     * @param FilesystemOperator $documentsStorage
     * @param DocumentInterface $document
     * @param array $attributes
     * @param bool $asObject Default false
     * @param string $imageUrl Only needed if you set $asObject to true.
     */
    public function __construct(
        FilesystemOperator $documentsStorage,
        DocumentInterface $document,
        array $attributes = [],
        bool $asObject = false,
        string $imageUrl = ""
    ) {
        $this->imageUrl = $imageUrl;
        $this->attributes = $attributes;
        $this->asObject = $asObject;
        $this->documentsStorage = $documentsStorage;
        $this->document = $document;
    }

    /**
     * Get SVG string to be used inside HTML content.
     *
     * @return string
     * @throws FilesystemException
     */
    public function getContent(): string
    {
        if (false === $this->asObject) {
            return $this->getInlineSvg();
        } else {
            return $this->getObjectSvg();
        }
    }

    /**
     * @return array
     */
    protected function getAllowedAttributes(): array
    {
        $attributes = [];
        foreach ($this->attributes as $key => $value) {
            if (in_array($key, static::$allowedAttributes)) {
                if ($key === 'identifier') {
                    $attributes['id'] = $value;
                } else {
                    $attributes[$key] = $value;
                }
            }
        }
        return $attributes;
    }

    /**
     * @return string
     * @throws FilesystemException
     */
    protected function getInlineSvg(): string
    {
        $mountPath = $this->document->getMountPath();

        if (null === $mountPath) {
            throw new FileNotFoundException('SVG document has no file');
        }

        if (!$this->documentsStorage->fileExists($mountPath)) {
            throw new FileNotFoundException('SVG file does not exist: ' . $mountPath);
        }
        // Create a new sanitizer instance
        $sanitizer = new Sanitizer();
        $sanitizer->minify(true);

        // Load the dirty svg
        $dirtySVG = $this->documentsStorage->read($mountPath);
        /**
         * @var string|false $cleanSVG
         */
        $cleanSVG = $sanitizer->sanitize($dirtySVG);
        if (false !== $cleanSVG) {
            // Pass it to the sanitizer and get it back clean
            return $this->injectAttributes($cleanSVG);
        }
        return $dirtySVG;
    }

    /**
     * @param string $svg
     * @return string
     * @throws \Exception
     */
    protected function injectAttributes(string $svg): string
    {
        $attributes = $this->getAllowedAttributes();
        if (count($attributes) > 0) {
            $xml = new \SimpleXMLElement($svg);
            $xml->registerXPathNamespace('svg', 'http://www.w3.org/2000/svg');
            $xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
            $xml->registerXPathNamespace('a', 'http://ns.adobe.com/AdobeSVGViewerExtensions/3.0/');
            $xml->registerXPathNamespace('ns1', 'http://ns.adobe.com/Flows/1.0/');
            $xml->registerXPathNamespace('ns0', 'http://ns.adobe.com/SaveForWeb/1.0/');
            $xml->registerXPathNamespace('ns', 'http://ns.adobe.com/Variables/1.0/');
            $xml->registerXPathNamespace('i', 'http://ns.adobe.com/AdobeIllustrator/10.0/');
            $xml->registerXPathNamespace('x', 'http://ns.adobe.com/Extensibility/1.0/');
            $xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
            $xml->registerXPathNamespace('cc', 'http://creativecommons.org/ns#');
            $xml->registerXPathNamespace('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
            $xml->registerXPathNamespace('sodipodi', 'http://sodipodi.sourceforge.net/DTD/sodipodi-0.dtd');
            $xml->registerXPathNamespace('inkscape', 'http://www.inkscape.org/namespaces/inkscape');

            foreach ($attributes as $key => $value) {
                if (isset($xml->attributes()->$key)) {
                    $xml->attributes()->$key = (string) $value;
                } else {
                    $xml->addAttribute($key, (string) $value);
                }
            }
            $svg = $xml->asXML();
        }
        if (false === $svg) {
            throw new \RuntimeException('Cannot inject attributes into SVG');
        }

        return \preg_replace('#^\<\?xml[^\?]+\?\>#', '', (string) $svg) ?? '';
    }

    /**
     * @return string
     * @deprecated Use SvgRenderer to render HTML object.
     */
    protected function getObjectSvg(): string
    {
        $mountPath = $this->document->getMountPath();

        if (null === $mountPath) {
            throw new FileNotFoundException('SVG document has no file');
        }

        $attributes = $this->getAllowedAttributes();
        $attributes['type'] = 'image/svg+xml';
        $attributes['data'] = $this->documentsStorage->publicUrl($mountPath);

        if (isset($attributes['alt'])) {
            unset($attributes['alt']);
        }

        $attrs = [];
        foreach ($attributes as $key => $value) {
            $attrs[] = $key . '="' . htmlspecialchars($value) . '"';
        }

        return '<object ' . implode(' ', $attrs) . '></object>';
    }
}
