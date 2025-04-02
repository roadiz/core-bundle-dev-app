<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\DocumentFinderInterface;
use RZ\Roadiz\Documents\Models\BaseDocumentInterface;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Twig\Environment;

class AudioRenderer extends AbstractRenderer
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

    public function supports(BaseDocumentInterface $document, array $options): bool
    {
        return $document->isAudio();
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(BaseDocumentInterface $document, array $options): string
    {
        $options = $this->viewOptionsResolver->resolve($options);
        $assignation = array_filter($options);
        $assignation['sources'] = $this->getSourcesFiles($document);

        return $this->renderHtmlElement('audio.html.twig', $assignation);
    }

    /**
     * Get sources files formats for audio and video documents.
     *
     * This method will search for document which filename is the same
     * except the extension. If you choose an MP4 file, it will look for a OGV and WEBM file.
     */
    protected function getSourcesFiles(BaseDocumentInterface $document): array
    {
        if (!$document->isLocal()) {
            return [];
        }

        return $this->getSourcesFilesArray(
            $document,
            $this->documentFinder->findAudiosWithFilename($document->getFilename())
        );
    }
}
