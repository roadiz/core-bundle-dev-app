<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class CloudflareProxyCache
{
    public function __construct(
        private readonly string $name,
        private readonly string $zone,
        private readonly string $version,
        private readonly string $bearer,
        private readonly string $email,
        private readonly string $key,
        private readonly int $timeout,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getZone(): string
    {
        return $this->zone;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getBearer(): string
    {
        return $this->bearer;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
