<?php

declare(strict_types=1);

namespace Themes\Rozier\Explorer;

use RZ\Roadiz\Core\AbstractEntities\PersistableInterface;
use RZ\Roadiz\CoreBundle\Entity\Document;
use RZ\Roadiz\CoreBundle\Explorer\AbstractExplorerItem;
use RZ\Roadiz\Documents\MediaFinders\EmbedFinderFactory;
use RZ\Roadiz\Documents\Models\AdvancedDocumentInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\Renderer\RendererInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DocumentExplorerItem extends AbstractExplorerItem
{
    public static array $thumbnail80Array = [
        'fit' => '80x80',
        'quality' => 50,
        'sharpen' => 5,
        'inline' => false,
    ];
    public static array $previewArray = [
        'width' => 1440,
        'quality' => 80,
        'inline' => false,
        'picture' => true,
        'embed' => true,
    ];

    public function __construct(
        private readonly DocumentInterface $document,
        private readonly RendererInterface $renderer,
        private readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly ?EmbedFinderFactory $embedFinderFactory = null,
    ) {
    }

    public function getId(): string|int
    {
        if ($this->document instanceof PersistableInterface) {
            return $this->document->getId();
        }

        return 0;
    }

    public function getAlternativeDisplayable(): ?string
    {
        return (string) $this->document;
    }

    public function getDisplayable(): string
    {
        if (
            $this->document instanceof Document
            && $this->document->getDocumentTranslations()->first()
            && $this->document->getDocumentTranslations()->first()->getName()
        ) {
            return $this->document->getDocumentTranslations()->first()->getName();
        }

        return (string) $this->document;
    }

    public function getOriginal(): DocumentInterface
    {
        return $this->document;
    }

    protected function getEditItemPath(): ?string
    {
        if (!($this->document instanceof PersistableInterface)) {
            return null;
        }

        return $this->urlGenerator->generate('documentsEditPage', [
            'documentId' => $this->document->getId(),
        ]);
    }

    protected function getColor(): ?string
    {
        if ($this->document instanceof AdvancedDocumentInterface) {
            return $this->document->getImageAverageColor();
        }

        return null;
    }

    public function toArray(): array
    {
        $thumbnail80Url = null;

        $this->documentUrlGenerator->setDocument($this->document);
        $hasThumbnail = false;

        if (
            $this->document instanceof HasThumbnailInterface
            && $this->document->needsThumbnail()
            && $this->document->hasThumbnails()
            && false !== $thumbnail = $this->document->getThumbnails()->first()
        ) {
            $this->documentUrlGenerator->setDocument($thumbnail);
            $hasThumbnail = true;
        }

        if (!$this->document->isPrivate() && !empty($this->document->getRelativePath())) {
            $this->documentUrlGenerator->setOptions(self::$thumbnail80Array);
            $thumbnail80Url = $this->documentUrlGenerator->getUrl();
            $this->documentUrlGenerator->setOptions(self::$previewArray);
        }

        $embedFinder = $this->embedFinderFactory?->createForPlatform(
            $this->document->getEmbedPlatform(),
            $this->document->getEmbedId()
        ) ?? null;

        return [
            ...parent::toArray(),
            'hasThumbnail' => $hasThumbnail,
            'isImage' => $this->document->isImage(),
            'isWebp' => 'image/webp' === $this->document->getMimeType(),
            'isVideo' => $this->document->isVideo(),
            'isSvg' => $this->document->isSvg(),
            'isEmbed' => $this->document->isEmbed(),
            'isPdf' => $this->document->isPdf(),
            'isPrivate' => $this->document->isPrivate(),
            'shortType' => $this->document->getShortType(),
            'processable' => $this->document->isProcessable(),
            'relativePath' => $this->document->getRelativePath(),
            'previewHtml' => !$this->document->isPrivate() ?
                $this->renderer->render($this->document, self::$previewArray) :
                null,
            'embedPlatform' => $this->document->getEmbedPlatform(),
            'icon' => null !== $embedFinder
                ? $embedFinder->getShortType()
                : $this->document->getShortType(),
            'shortMimeType' => $this->document->getShortMimeType(),
            'thumbnail80' => $thumbnail80Url,
        ];
    }
}