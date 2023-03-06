<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

interface TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generateToken();
}
