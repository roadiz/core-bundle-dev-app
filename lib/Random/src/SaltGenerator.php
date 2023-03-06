<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

class SaltGenerator extends RandomGenerator implements SaltGeneratorInterface
{
    /**
     * @return string
     */
    public function generateSalt()
    {
        return strtr(base64_encode($this->getRandomNumber(24)), '{}', '-_');
    }
}
