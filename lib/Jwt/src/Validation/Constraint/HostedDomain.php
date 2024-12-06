<?php

declare(strict_types=1);

namespace RZ\Roadiz\JWT\Validation\Constraint;

use Lcobucci\JWT\Token;
use Lcobucci\JWT\Token\Plain;
use Lcobucci\JWT\Validation\Constraint;
use Lcobucci\JWT\Validation\ConstraintViolation;

final readonly class HostedDomain implements Constraint
{
    public function __construct(private string $hostedDomain)
    {
    }

    public function assert(Token $token): void
    {
        if (!$token instanceof Plain || empty($this->hostedDomain)) {
            return;
        }
        if (!$token->claims()->has('hd')) {
            throw new ConstraintViolation('Token does not expose any Hosted Domain.');
        }
        /*
         * Check that Hosted Domain is the same as required by Roadiz
         */
        if ($token->claims()->get('hd') !== $this->hostedDomain) {
            throw new ConstraintViolation('User ('.$token->claims()->get('hd').') does not belong to Hosted Domain.');
        }
    }
}
