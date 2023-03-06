<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

class TokenGenerator extends RandomGenerator implements TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode($this->getRandomNumber()), '+/', '-_'), '=');
    }
}
