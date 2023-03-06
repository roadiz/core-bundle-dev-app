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
    protected Environment $templating;
    protected DocumentUrlGeneratorInterface $documentUrlGenerator;
    protected string $templateBasePath;
    protected UrlOptionsResolver $urlOptionsResolver;
    protected ViewOptionsResolver $viewOptionsResolver;
    protected FilesystemOperator $documentsStorage;

    public function __construct(
        FilesystemOperator $documentsStorage,
        Environment $templating,
        DocumentUrlGeneratorInterface $documentUrlGenerator,
        string $templateBasePath = 'documents'
    ) {
        $this->documentsStorage = $documentsStorage;
        $this->templating = $templating;
        $this->documentUrlGenerator = $documentUrlGenerator;
        $this->templateBasePath = $templateBasePath;
        $this->urlOptionsResolver = new UrlOptionsResolver();
        $this->viewOptionsResolver = new ViewOptionsResolver();
    }

    /**
     * @param DocumentInterface $document
     * @param array $options
     *
     * @return string
     */
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
     * @param string $template
     * @param array $assignation
     *
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    protected function renderHtmlElement(string $template, array $assignation): string
    {
        return $this->templating->render($this->templateBasePath . '/' . $template, $assignation);
    }

    /**
     * @param DocumentInterface $document
     * @param iterable<DocumentInterface> $sourcesDocs
     * @return array
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

        if (count($sources) === 0) {
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
