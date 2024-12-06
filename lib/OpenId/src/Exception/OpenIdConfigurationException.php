<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Exception;

final class OpenIdConfigurationException extends \RuntimeException
{
    public function __construct(
        string $message = 'OpenID configuration is not valid',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
