<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

interface TokenGeneratorInterface
{
    public function generateToken(): string;
}
