<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

interface PasswordGeneratorInterface
{
    /**
     * @return string
     */
    public function generatePassword(int $length = 12);
}
