<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

use Psr\Cache\CacheItemPoolInterface;

final class NodesSourcesUrlsCacheClearer extends FileClearer
{
    public function __construct(private readonly CacheItemPoolInterface $cacheProvider)
    {
        parent::__construct('');
    }

    public function clear(): bool
    {
        $this->output .= 'Node-sources URLs cache: ';

        if ($this->cacheProvider->clear()) {
            $this->output .= 'cleared';

            return true;
        }

        return false;
    }
}
