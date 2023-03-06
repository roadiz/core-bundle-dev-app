<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache\Clearer;

use Psr\Cache\CacheItemPoolInterface;

final class NodesSourcesUrlsCacheClearer extends FileClearer
{
    private CacheItemPoolInterface $cacheProvider;

    public function __construct(CacheItemPoolInterface $cacheProvider)
    {
        parent::__construct('');
        $this->cacheProvider = $cacheProvider;
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
