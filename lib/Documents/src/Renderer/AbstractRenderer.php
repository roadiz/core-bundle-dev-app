<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use League\Flysystem\FilesystemOperator;
use RZ\Roadiz\Documents\Exceptions\DocumentWithoutFileException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\OptionsResolver\UrlOptionsResolver;
use RZ\Roadiz\Documents\OptionsResolver\ViewOptionsResolver;
use RZ\Roadiz\Documents\UrlGenerators\DocumentUrlGeneratorInterface;
use Twig\Environment;

abstract class AbstractRenderer implements RendererInterface
{
    protected UrlOptionsResolver $urlOptionsResolver;
    protected ViewOptionsResolver $viewOptionsResolver;

    public function __construct(
        protected readonly FilesystemOperator $documentsStorage,
        protected readonly Environment $templating,
        protected readonly DocumentUrlGeneratorInterface $documentUrlGenerator,
        protected readonly string $templateBasePath = 'documents',
    ) {
        $this->urlOptionsResolver = new UrlOptionsResolver();
        $this->viewOptionsResolver = new ViewOptionsResolver();
    }

    protected function getSource(DocumentInterface $document, array $options): string
    {
        if (empty($document->getRelativePath())) {
            throw new DocumentWithoutFileException($document);
        }
        $this->documentUrlGenerator->setOptions($options);
        $this->documentUrlGenerator->setDocument($document);

        return $this->documentUrlGenerator->getUrl($options['absolute']);
    }

    /**
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function renderHtmlElement(string $template, array $assignation): string
    {
        return $this->templating->render($this->templateBasePath.'/'.$template, $assignation);
    }

    /**
     * @param iterable<DocumentInterface> $sourcesDocs
     */
    protected function getSourcesFilesArray(DocumentInterface $document, iterable $sourcesDocs): array
    {
        $sources = [];

        /**
         * @var DocumentInterface $source
         */
        foreach ($sourcesDocs as $source) {
            $sourceMountPath = $source->getMountPath();
            if (null !== $sourceMountPath) {
                $sources[$source->getMimeType()] = [
                    'mime' => $source->getMimeType(),
                    'url' => $this->documentsStorage->publicUrl($sourceMountPath),
                ];
            }
        }
        krsort($sources);

        if (0 === count($sources)) {
            // If exotic extension, fallbacks using original file
            $documentMountPath = $document->getMountPath();
            if (null !== $documentMountPath) {
                $sources[$document->getMimeType()] = [
                    'mime' => $document->getMimeType(),
                    'url' => $this->documentsStorage->publicUrl($documentMountPath),
                ];
            }
        }

        return $sources;
    }
}
