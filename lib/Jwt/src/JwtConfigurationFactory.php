<?php

declare(strict_types=1);

namespace RZ\Roadiz\JWT;

use Lcobucci\JWT\Configuration;

interface JwtConfigurationFactory
{
    /**
     * @param string|null $kid Key ID to select the matching verification key when several are available (e.g. JWKS rotation). Implementations may ignore it.
     */
    public function create(?string $kid = null): ?Configuration;
}
