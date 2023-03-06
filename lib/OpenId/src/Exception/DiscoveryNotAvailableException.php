<?php

declare(strict_types=1);

namespace RZ\Roadiz\OpenId\Exception;

use Throwable;

final class DiscoveryNotAvailableException extends \RuntimeException
{
    /**
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = 'OpenID discovery is not configured', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
