<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Exception;

use Throwable;

final class DiscoveryNotAvailableException extends \RuntimeException
{
    public function __construct(
        string $message = 'OpenID discovery is not configured',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
