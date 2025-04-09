<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Models\BaseDocumentInterface;

interface RendererInterface
{
    public function supports(BaseDocumentInterface $document, array $options): bool;

    public function render(BaseDocumentInterface $document, array $options): string;
}
