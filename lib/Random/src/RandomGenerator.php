<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

use Psr\Log\LoggerInterface;

class RandomGenerator
{
    public function __construct(protected readonly LoggerInterface $logger)
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new \RuntimeException('You must enable the "openssl" extension for secure random number generation.');
        }
    }

    public function getRandomNumber(int $nbBytes = 32): string
    {
        // try OpenSSL
        $bytes = \openssl_random_pseudo_bytes($nbBytes, $strong);

        if (true === $strong) {
            return $bytes;
        }

        throw new \RuntimeException('Unable to generate a secure random number.');
    }
}
