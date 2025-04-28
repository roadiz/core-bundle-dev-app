<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\UrlGenerators;

use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

class DummyDocumentUrlGenerator implements DocumentUrlGeneratorInterface
{
    private ?BaseDocumentInterface $document = null;
    private array $options = [];

    public function getUrl(bool $absolute = false): string
    {
        if (null === $this->document) {
            throw new \BadMethodCallException('Document is null');
        }
        if (!key_exists('noProcess', $this->options)) {
            throw new \BadMethodCallException('noProcess option is not set');
        }

        if (true === $this->options['noProcess'] || !$this->document->isProcessable()) {
            $path = '/files/'.$this->document->getRelativePath();

            return ($absolute) ? ('http://dummy.test'.$path) : ($path);
        }

        $compiler = new OptionsCompiler();
        $compiledOptions = $compiler->compile($this->options);

        if ($absolute) {
            return 'http://dummy.test/assets/'.$compiledOptions.'/'.$this->document->getRelativePath();
        }

        return '/assets/'.$compiledOptions.'/'.$this->document->getRelativePath();
    }

    public function setDocument(BaseDocumentInterface $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function setOptions(array $options = []): static
    {
        $this->options = $options;

        return $this;
    }
}
