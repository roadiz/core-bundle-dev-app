<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Cache;

final class ReverseProxyCache
{
    protected string $name;
    protected string $host;
    protected string $domainName;
    protected int $timeout;

    /**
     * @param string $name
     * @param string $host
     * @param string $domainName
     * @param int $timeout
     */
    public function __construct(string $name, string $host, string $domainName, int $timeout)
    {
        $this->name = $name;
        $this->host = $host;
        $this->domainName = $domainName;
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
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getDomainName(): string
    {
        return $this->domainName;
    }
}
