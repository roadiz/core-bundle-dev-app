<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

class TokenGenerator extends RandomGenerator implements TokenGeneratorInterface
{
    public function generateToken(): string
    {
        return rtrim(strtr(base64_encode($this->getRandomNumber()), '+/', '-_'), '=');
    }
}
