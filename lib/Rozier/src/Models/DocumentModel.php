<?php

declare(strict_types=1);

namespace Themes\Rozier\Models;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\Renderer\RendererInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @package Themes\Rozier\Models
 */
final class DocumentModel implements ModelInterface
{
    public static array $thumbnailArray = [
        "fit" => "40x40",
        "quality" => 50,
        "sharpen" => 5,
        "inline" => false,
    ];
    public static array $thumbnail80Array = [
        "fit" => "80x80",
        "quality" => 50,
        "sharpen" => 5,
        "inline" => false,
    ];
    public static array $previewArray = [
        "width" => 1440,
        "quality" => 80,
        "inline" => false,
        "embed" => true,
    ];
    public static array $largeArray = [
        "noProcess" => true,
    ];

    private DocumentInterface $document;
    private RendererInterface $renderer;
    private DocumentUrlGeneratorInterface $documentUrlGenerator;
    private UrlGeneratorInterface $urlGenerator;
    private ?EmbedFinderFactory $embedFinderFactory;

    /**
     * @param DocumentInterface $document
     * @param RendererInterface $renderer
     * @param DocumentUrlGeneratorInterface $documentUrlGenerator
     * @param UrlGeneratorInterface $urlGenerator
     * @param EmbedFinderFactory|null $embedFinderFactory
     */
    public function __construct(
        DocumentInterface $document,
        RendererInterface $renderer,
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        UrlGeneratorInterface $urlGenerator,
        ?EmbedFinderFactory $embedFinderFactory = null
    ) {
        $this->document = $document;
        $this->renderer = $renderer;
        $this->documentUrlGenerator = $documentUrlGenerator;
        $this->urlGenerator = $urlGenerator;
        $this->embedFinderFactory = $embedFinderFactory;
    }

    public function toArray(): array
    {
        $name = (string) $this->document;
        $thumbnail80Url = null;
        $previewUrl = null;

        if (
            $this->document instanceof Document &&
            $this->document->getDocumentTranslations()->first() &&
            $this->document->getDocumentTranslations()->first()->getName()
        ) {
            $name = $this->document->getDocumentTranslations()->first()->getName();
        }

        $this->documentUrlGenerator->setDocument($this->document);
        $hasThumbnail = false;

        if (
            $this->document instanceof HasThumbnailInterface &&
            $this->document->needsThumbnail() &&
            $this->document->hasThumbnails() &&
            false !== $thumbnail = $this->document->getThumbnails()->first()
        ) {
            $this->documentUrlGenerator->setDocument($thumbnail);
            $hasThumbnail = true;
        }

        if (!$this->document->isPrivate() && !empty($this->document->getRelativePath())) {
            $this->documentUrlGenerator->setOptions(DocumentModel::$thumbnail80Array);
            $thumbnail80Url = $this->documentUrlGenerator->getUrl();
            $this->documentUrlGenerator->setOptions(DocumentModel::$previewArray);
            $previewUrl = $this->documentUrlGenerator->getUrl();
        }

        if ($this->document instanceof PersistableInterface) {
            $id = $this->document->getId();
            $editUrl = $this->urlGenerator
                ->generate('documentsEditPage', [
                    'documentId' => $this->document->getId()
                ]);
        } else {
            $id = null;
            $editUrl = null;
        }

        $embedFinder = $this->embedFinderFactory?->createForPlatform(
            $this->document->getEmbedPlatform(),
            $this->document->getEmbedId()
        ) ?? null;

        return [
            'id' => $id,
            'filename' => (string) $this->document,
            'name' => $name,
            'hasThumbnail' => $hasThumbnail,
            'isImage' => $this->document->isImage(),
            'isWebp' => $this->document->getMimeType() === 'image/webp',
            'isVideo' => $this->document->isVideo(),
            'isSvg' => $this->document->isSvg(),
            'isEmbed' => $this->document->isEmbed(),
            'isPdf' => $this->document->isPdf(),
            'isPrivate' => $this->document->isPrivate(),
            'shortType' => $this->document->getShortType(),
            'processable' => $this->document->isProcessable(),
            'relativePath' => $this->document->getRelativePath(),
            'editUrl' => $editUrl,
            'preview' => $previewUrl,
            'preview_html' => !$this->document->isPrivate() ?
                $this->renderer->render($this->document, DocumentModel::$previewArray) :
                null,
            'embedPlatform' => $this->document->getEmbedPlatform(),
            'icon' => null !== $embedFinder
                ? $embedFinder->getShortType()
                : $this->document->getShortType(),
            'shortMimeType' => $this->document->getShortMimeType(),
            'thumbnail_80' => $thumbnail80Url,
            'url' => $previewUrl ?? $thumbnail80Url ?? null,
        ];
    }
}
