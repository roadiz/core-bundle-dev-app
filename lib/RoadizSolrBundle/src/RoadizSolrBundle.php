<?php

declare(strict_types=1);

namespace RZ\Roadiz\SolrBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class RoadizSolrBundle extends Bundle
{
    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
