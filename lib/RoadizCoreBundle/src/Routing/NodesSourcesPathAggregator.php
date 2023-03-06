<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Routing;

use RZ\Roadiz\CoreBundle\Entity\NodesSources;

interface NodesSourcesPathAggregator
{
    public function aggregatePath(NodesSources $nodesSources, array $parameters = []): string;
}
