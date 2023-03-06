<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class CloudflareProxyCache
{
    protected string $name;
    protected string $zone;
    protected string $version;
    protected string $bearer;
    protected string $email;
    protected string $key;
    protected int $timeout;

    /**
     * @param string $name
     * @param string $zone
     * @param string $version
     * @param string $bearer
     * @param string $email
     * @param string $key
     * @param int $timeout
     */
    public function __construct(string $name, string $zone, string $version, string $bearer, string $email, string $key, int $timeout)
    {
        $this->name = $name;
        $this->zone = $zone;
        $this->version = $version;
        $this->bearer = $bearer;
        $this->email = $email;
        $this->key = $key;
        $this->timeout = $timeout;
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
