<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;

/**
 * Fallback document render to its first thumbnail.
 */
class ThumbnailRenderer implements RendererInterface
{
    protected ?ChainRenderer $chainRenderer = null;

    public function __construct(?ChainRenderer $chainRenderer = null)
    {
        $this->chainRenderer = $chainRenderer;
    }

    public function supports(DocumentInterface $document, array $options): bool
    {
        return null !== $this->chainRenderer
            && (!key_exists('embed', $options)
            || true !== $options['embed'])
            && $document instanceof HasThumbnailInterface
            && $document->hasThumbnails()
            && false !== $document->getThumbnails()->first();
    }

    public function render(DocumentInterface $document, array $options): string
    {
        if (
            null !== $this->chainRenderer
            && $document instanceof HasThumbnailInterface
        ) {
            $thumbnail = $document->getThumbnails()->first();
            if ($thumbnail instanceof DocumentInterface) {
                return $this->chainRenderer->render($thumbnail, $options);
            }
        }

        return '';
    }
}
