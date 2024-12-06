<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

interface PasswordGeneratorInterface
{
    public function generatePassword(int $length = 16): string;
}
