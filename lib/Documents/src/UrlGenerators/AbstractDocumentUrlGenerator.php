<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\UrlGenerators;

use League\Flysystem\FilesystemOperator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\OptionsResolver\ViewOptionsResolver;
use Symfony\Component\HttpFoundation\UrlHelper;

abstract class AbstractDocumentUrlGenerator implements DocumentUrlGeneratorInterface
{
    protected ?DocumentInterface $document;
    protected array $options;
    protected CacheItemPoolInterface $optionsCacheAdapter;
    protected ViewOptionsResolver $viewOptionsResolver;
    protected OptionsCompiler $optionCompiler;
    protected FilesystemOperator $documentsStorage;
    private UrlHelper $urlHelper;

    /**
     * @param FilesystemOperator $documentsStorage
     * @param UrlHelper $urlHelper
     * @param CacheItemPoolInterface $optionsCacheAdapter
     * @param DocumentInterface|null $document
     * @param array $options
     * @throws InvalidArgumentException
     */
    public function __construct(
        FilesystemOperator $documentsStorage,
        UrlHelper $urlHelper,
        CacheItemPoolInterface $optionsCacheAdapter,
        DocumentInterface $document = null,
        array $options = []
    ) {
        $this->document = $document;
        $this->viewOptionsResolver = new ViewOptionsResolver();
        $this->optionCompiler = new OptionsCompiler();
        $this->optionsCacheAdapter = $optionsCacheAdapter;

        $this->setOptions($options);
        $this->documentsStorage = $documentsStorage;
        $this->urlHelper = $urlHelper;
    }

    /**
     * @param array $options
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setOptions(array $options = []): static
    {
        $optionsCacheItem = $this->optionsCacheAdapter->getItem(md5(json_encode($options) ?: ''));
        if (!$optionsCacheItem->isHit()) {
            $resolvedOptions = $this->viewOptionsResolver->resolve($options);
            $optionsCacheItem->set($resolvedOptions);
            $this->optionsCacheAdapter->save($optionsCacheItem);
        }

        $cachedOptions = $optionsCacheItem->get();
        $this->options = is_array($cachedOptions) ? $cachedOptions : [];

        return $this;
    }

    /**
     * @return DocumentInterface|null
     */
    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    /**
     * @param DocumentInterface $document
     *
     * @return $this
     */
    public function setDocument(DocumentInterface $document): static
    {
        $this->document = $document;
        return $this;
    }

    /**
     * @param bool $absolute
     *
     * @return string
     */
    public function getUrl(bool $absolute = false): string
    {
        if (null === $this->document) {
            throw new \InvalidArgumentException('Cannot get URL from a NULL document');
        }

        $mountPath = $this->document->getMountPath();

        if ($this->document->isPrivate()) {
            throw new \InvalidArgumentException('Cannot get URL from a private document');
        }

        if (null !== $mountPath && ($this->options['noProcess'] === true || !$this->document->isProcessable())) {
            $publicUrl = $this->documentsStorage->publicUrl($mountPath);
            if ($absolute && \str_starts_with($publicUrl, '/')) {
                return $this->urlHelper->getAbsoluteUrl($publicUrl);
            } else {
                return $publicUrl;
            }
        }

        return $this->getProcessedDocumentUrlByArray($absolute);
    }

    /**
     * @param  bool $absolute
     * @return string
     */
    abstract protected function getProcessedDocumentUrlByArray(bool $absolute = false): string;
}
