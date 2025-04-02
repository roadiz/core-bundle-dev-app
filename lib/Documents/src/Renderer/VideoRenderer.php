<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\Models\HasThumbnailInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Twig\Environment;

class VideoRenderer extends AbstractRenderer
{
    public function __construct(
        FilesystemOperator $documentsStorage,
        protected readonly DocumentFinderInterface $documentFinder,
        Environment $templating,
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        string $templateBasePath = 'documents',
    ) {
        parent::__construct($documentsStorage, $templating, $documentUrlGenerator, $templateBasePath);
    }

    public function supports(DocumentInterface $document, array $options): bool
    {
        return $document->isVideo();
    }

    /**
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     * @throws \Twig\Error\LoaderError
     */
    public function render(DocumentInterface $document, array $options): string
    {
        $options = $this->viewOptionsResolver->resolve($options);

        $assignation = array_filter($options);
        $assignation['sources'] = $this->getSourcesFiles($document);

        /*
         * Use a user defined poster url
         */
        if (!empty($options['custom_poster'])) {
            $assignation['poster'] = trim(strip_tags($options['custom_poster']));
        } else {
            /*
             * Look for poster with the same args as the video.
             */
            $assignation['poster'] = $this->getPosterUrl($document, $options, $options['absolute']);
        }

        return $this->renderHtmlElement('video.html.twig', $assignation);
    }

    protected function getPosterUrl(
        DocumentInterface $document,
        array $options = [],
        bool $absolute = false,
    ): ?string {
        /*
         * Use document thumbnail first
         */
        if (
            !$options['no_thumbnail']
            && $document instanceof HasThumbnailInterface
            && $document->hasThumbnails()
        ) {
            $thumbnail = $document->getThumbnails()->first();
            if (false !== $thumbnail) {
                $this->documentUrlGenerator->setOptions($options);
                $this->documentUrlGenerator->setDocument($thumbnail);

                return $this->documentUrlGenerator->getUrl($absolute);
            }
        }
        /*
         * Then look for document with same filename
         */
        if (!$document->isLocal()) {
            return null;
        }

        $sourcesDocs = $this->documentFinder->findPicturesWithFilename($document->getFilename());

        foreach ($sourcesDocs as $sourcesDoc) {
            $this->documentUrlGenerator->setOptions($options);
            $this->documentUrlGenerator->setDocument($sourcesDoc);

            return $this->documentUrlGenerator->getUrl($absolute);
        }

        return null;
    }

    /**
     * Get sources files formats for audio and video documents.
     *
     * This method will search for document which filename is the same
     * except the extension. If you choose an MP4 file, it will look for a OGV and WEBM file.
     */
    protected function getSourcesFiles(DocumentInterface $document): array
    {
        if (!$document->isLocal()) {
            return [];
        }

        return $this->getSourcesFilesArray(
            $document,
            $this->documentFinder->findVideosWithFilename($document->getFilename())
        );
    }
}
