<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\UrlGenerators;

use League\Flysystem\FilesystemOperator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use RZ\Roadiz\Documents\Exceptions\PrivateDocumentException;
use RZ\Roadiz\Documents\Models\DocumentInterface;
use RZ\Roadiz\Documents\OptionsResolver\ViewOptionsResolver;
use Symfony\Component\HttpFoundation\UrlHelper;

abstract class AbstractDocumentUrlGenerator implements DocumentUrlGeneratorInterface
{
    protected ?DocumentInterface $document;
    protected array $options;
    protected ViewOptionsResolver $viewOptionsResolver;
    protected OptionsCompiler $optionCompiler;

    public function __construct(
        protected FilesystemOperator $documentsStorage,
        protected UrlHelper $urlHelper,
        protected CacheItemPoolInterface $optionsCacheAdapter,
        array $options = [],
    ) {
        $this->viewOptionsResolver = new ViewOptionsResolver();
        $this->optionCompiler = new OptionsCompiler();
        $this->setOptions($options);
    }

    /**
     * @return $this
     *
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

    public function getDocument(): ?DocumentInterface
    {
        return $this->document;
    }

    /**
     * @return $this
     */
    public function setDocument(DocumentInterface $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getUrl(bool $absolute = false): string
    {
        if (null === $this->document) {
            throw new \InvalidArgumentException('Cannot get URL from a NULL document');
        }
        if ($this->document->isPrivate()) {
            throw new PrivateDocumentException('Cannot get URL from a private document');
        }

        $mountPath = $this->document->getMountPath();

        if (null !== $mountPath && (true === $this->options['noProcess'] || !$this->document->isProcessable())) {
            $publicUrl = $this->documentsStorage->publicUrl($mountPath);
            if ($absolute && \str_starts_with($publicUrl, '/')) {
                return $this->urlHelper->getAbsoluteUrl($publicUrl);
            } else {
                return $publicUrl;
            }
        }

        return $this->getProcessedDocumentUrlByArray($absolute);
    }

    abstract protected function getProcessedDocumentUrlByArray(bool $absolute = false): string;
}
