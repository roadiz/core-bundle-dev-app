<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final readonly class CloudflareProxyCache
{
    public function __construct(
        private string $name,
        private string $zone,
        private string $version,
        private string $bearer,
        private string $email,
        private string $key,
        private int $timeout,
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
