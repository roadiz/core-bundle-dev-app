<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class ReverseProxyCacheLocator
{
    /**
     * @param ReverseProxyCache[] $frontends
     * @param CloudflareProxyCache|null $cloudflareProxyCache
     */
    public function __construct(
        private readonly array $frontends,
        private readonly ?CloudflareProxyCache $cloudflareProxyCache = null
    ) {
    }

    /**
     * @return ReverseProxyCache[]
     */
    public function getFrontends(): array
    {
        return $this->frontends;
    }

    /**
     * @return CloudflareProxyCache|null
     */
    public function getCloudflareProxyCache(): ?CloudflareProxyCache
    {
        return $this->cloudflareProxyCache;
    }
}
