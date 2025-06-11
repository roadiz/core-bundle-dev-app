<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

class SaltGenerator extends RandomGenerator implements SaltGeneratorInterface
{
    #[\Override]
    public function generateSalt(): string
    {
        return strtr(base64_encode($this->getRandomNumber(24)), '{}', '-_');
    }
}
