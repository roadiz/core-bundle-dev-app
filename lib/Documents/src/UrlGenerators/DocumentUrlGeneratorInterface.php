<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\UrlGenerators;

use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

interface DocumentUrlGeneratorInterface
{
    public function getUrl(bool $absolute = false): string;

    /**
     * @return $this
     */
    public function setDocument(BaseDocumentInterface $document): static;

    /**
     * @return $this
     */
    public function setOptions(array $options = []): static;
}
