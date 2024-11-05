<?php

declare(strict_types=1);

namespace RZ\Roadiz\Documents\Renderer;

use RZ\Roadiz\Documents\Models\DocumentInterface;

interface RendererInterface
{
    public function supports(DocumentInterface $document, array $options): bool;

    public function render(DocumentInterface $document, array $options): string;
}
