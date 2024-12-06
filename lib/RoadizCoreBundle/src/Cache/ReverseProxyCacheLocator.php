<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final readonly class ReverseProxyCacheLocator
{
    /**
     * @param ReverseProxyCache[] $frontends
     */
    public function __construct(
        private array $frontends,
        private ?CloudflareProxyCache $cloudflareProxyCache = null,
    ) {
    }

    /**
     * @return ReverseProxyCache[]
     */
    public function getFrontends(): array
    {
        return $this->frontends;
    }

    public function getCloudflareProxyCache(): ?CloudflareProxyCache
    {
        return $this->cloudflareProxyCache;
    }
}
