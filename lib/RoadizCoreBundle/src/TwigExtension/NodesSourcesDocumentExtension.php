<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\TwigExtension;

use RZ\Roadiz\CoreBundle\Entity\NodesSourcesDocuments;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

final class NodesSourcesDocumentExtension extends AbstractExtension
{
    public function getTests(): array
    {
        return [new TwigTest('NodesSourcesDocument', function ($mixed) {
            return $mixed instanceof NodesSourcesDocuments;
        })];
    }
}
