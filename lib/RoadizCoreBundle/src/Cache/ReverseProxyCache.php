<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class ReverseProxyCache
{
    public function __construct(
        private readonly string $name,
        private readonly string $host,
        private readonly string $domainName,
        private readonly int $timeout,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDomainName(): string
    {
        return $this->domainName;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
