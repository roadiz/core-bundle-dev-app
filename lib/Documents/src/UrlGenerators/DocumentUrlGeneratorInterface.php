<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\UrlGenerators;

use RZ\Roadiz\Documents\Models\DocumentInterface;

interface DocumentUrlGeneratorInterface
{
    public function getUrl(bool $absolute = false): string;

    /**
     * @return $this
     */
    public function setDocument(DocumentInterface $document): static;

    /**
     * @return $this
     */
    public function setOptions(array $options = []): static;
}
