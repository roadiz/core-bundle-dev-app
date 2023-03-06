<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class ReverseProxyCacheLocator
{
    /**
     * @var ReverseProxyCache[]
     */
    private array $frontends;
    private ?CloudflareProxyCache $cloudflareProxyCache;

    /**
     * @param ReverseProxyCache[] $frontends
     * @param CloudflareProxyCache|null $cloudflareProxyCache
     */
    public function __construct(array $frontends, ?CloudflareProxyCache $cloudflareProxyCache = null)
    {
        $this->frontends = $frontends;
        $this->cloudflareProxyCache = $cloudflareProxyCache;
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
