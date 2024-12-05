<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final readonly class ReverseProxyCache
{
    public function __construct(
        private string $name,
        private string $host,
        private string $domainName,
        private int $timeout,
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
