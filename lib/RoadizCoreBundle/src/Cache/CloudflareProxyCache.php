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
        private readonly int $timeout
    ) {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getZone(): string
    {
        return $this->zone;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getBearer(): string
    {
        return $this->bearer;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }
}
