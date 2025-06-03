<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Twig\Environment;

abstract class AbstractImageRenderer extends AbstractRenderer
{
    public const WIDTH_HEIGHT_PATTERN = '#(?<width>[0-9]+)[x:\.](?<height>[0-9]+)#';

    public function __construct(
        FilesystemOperator $documentsStorage,
        protected readonly EmbedFinderFactory $embedFinderFactory,
        Environment $templating,
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        string $templateBasePath = 'documents',
    ) {
        parent::__construct($documentsStorage, $templating, $documentUrlGenerator, $templateBasePath);
    }

    #[\Override]
    public function supports(BaseDocumentInterface $document, array $options): bool
    {
        return $document->isImage()
            && !empty($document->getRelativePath())
            && !$this->isEmbeddable($document, $options);
    }

    public function isEmbeddable(BaseDocumentInterface $document, array $options): bool
    {
        return isset($options['embed'])
            && true === $options['embed']
            && null !== $document->getEmbedPlatform()
            && $this->embedFinderFactory->supports($document->getEmbedPlatform());
    }

    protected function parseSizes(array $options = []): ?string
    {
        if (count($options['sizes']) > 0) {
            return implode(', ', $options['sizes']);
        }

        return null;
    }

    protected function willResample(array &$assignation): bool
    {
        return !empty($assignation['fit'])
            || !empty($assignation['crop'])
            || !empty($assignation['rotate'])
            || !empty($assignation['width'])
            || !empty($assignation['height']);
    }

    protected function parseSrcSet(
        BaseDocumentInterface $document,
        array $options = [],
        bool $convertToWebP = false,
    ): ?string {
        if (count($options['srcset']) > 0) {
            return $this->parseSrcSetInner($document, $options['srcset'], $convertToWebP, $options['absolute']);
        }

        return null;
    }

    protected function parseSrcSetInner(
        BaseDocumentInterface $document,
        array $srcSetArray = [],
        bool $convertToWebP = false,
        bool $absolute = false,
    ): string {
        $output = [];
        foreach ($srcSetArray as $set) {
            if (
                isset($set['format'])
                && isset($set['rule'])
                && !$document->isPrivate()
                && !empty($document->getRelativePath())
            ) {
                $this->documentUrlGenerator->setOptions($this->urlOptionsResolver->resolve($set['format']));
                $this->documentUrlGenerator->setDocument($document);
                $path = $this->documentUrlGenerator->getUrl($absolute);
                if ($convertToWebP) {
                    $path .= '.webp';
                }
                $output[] = $path.' '.$set['rule'];
            }
        }

        return implode(', ', $output);
    }

    protected function createTransparentDataURI(string $hexColor, int $width = 1, int $height = 1): string
    {
        $hexColorArray = \sscanf($hexColor, '#%02x%02x%02x');
        if (null === $hexColorArray) {
            throw new \RuntimeException('Color is not a valid hexadecimal RGB format');
        }
        [$r, $g, $b] = $hexColorArray;
        $im = \imagecreatetruecolor($width, $height);
        if ($im) {
            \imagefill(
                $im,
                0,
                0,
                \imagecolorallocate($im, $r ?? 0, $g ?? 0, $b ?? 0) ?: 0
            );
            \ob_start();
            \imagejpeg($im, null, 30);
            $img = \ob_get_contents();
            \ob_end_clean();
            if ($img) {
                return 'data:image/jpeg;base64,'.\base64_encode($img);
            }
        }
        throw new \RuntimeException('Cannot generate imageCreateTrueColor');
    }

    protected function getImageRatio(array &$options): ?float
    {
        /** @var \ArrayAccess<string, string|null> $options */
        $compositing = $options['crop'] ?? $options['fit'] ?? '';
        if (1 === preg_match(static::WIDTH_HEIGHT_PATTERN, $compositing, $matches)) {
            return ((float) $matches['width']) / ((float) $matches['height']);
        }

        return null;
    }

    protected function getImageWidth(array &$options): int
    {
        /** @var \ArrayAccess<string, string|null> $options */
        $compositing = $options['fit'] ?? '';
        if (1 === preg_match(static::WIDTH_HEIGHT_PATTERN, $compositing, $matches)) {
            return (int) $matches['width'];
        } elseif (null !== $options['ratio'] && 0 !== $options['height'] && 0 !== $options['ratio']) {
            return (int) (intval($options['height']) * floatval($options['ratio']));
        }

        return 0;
    }

    protected function getImageHeight(array &$options): int
    {
        /** @var \ArrayAccess<string, string|null> $options */
        $compositing = $options['fit'] ?? '';
        if (1 === preg_match(static::WIDTH_HEIGHT_PATTERN, $compositing, $matches)) {
            return (int) $matches['height'];
        } elseif (null !== $options['ratio'] && 0 !== $options['width'] && 0 !== $options['ratio']) {
            return (int) (intval($options['width']) / floatval($options['ratio']));
        }

        return 0;
    }

    protected function additionalAssignation(BaseDocumentInterface $document, array $options, array &$assignation): void
    {
        /*
         * Guess ratio, width and height options from fit or crop
         */
        if (empty($options['ratio'])) {
            $assignation['ratio'] = $options['ratio'] = $this->getImageRatio($options);
        }
        if (empty($options['width'])) {
            $assignation['width'] = $options['width'] = $this->getImageWidth($options);
        }
        if (empty($options['height'])) {
            $assignation['height'] = $options['height'] = $this->getImageHeight($options);
        }

        if (!($document instanceof AdvancedDocumentInterface)) {
            return;
        }

        if (null !== $options['ratio'] && 0 !== $options['ratio']) {
            $assignation['ratio'] = $options['ratio'];
        } elseif (null !== $document->getImageRatio()) {
            $assignation['ratio'] = $document->getImageRatio();
        }
        if (
            null !== $document->getImageAverageColor()
            && '#ffffff' !== $document->getImageAverageColor()
            && '#000000' !== $document->getImageAverageColor()
        ) {
            $assignation['averageColor'] = $document->getImageAverageColor();
        }
        if (true === $options['blurredFallback']) {
            if (!empty($options['fit'])) {
                // Both Fit and Width cannot be explicitly set
                // need to revert on Crop
                $options['crop'] = $options['fit'];
                unset($options['fit']);
            }
            if (!empty($options['height'])) {
                unset($options['height']);
            }
            $assignation['fallback'] = $this->getSource(
                $document,
                array_merge(
                    $options,
                    [
                        'quality' => 10,
                        'width' => 60,
                    ]
                )
            );
        }
    }
}
