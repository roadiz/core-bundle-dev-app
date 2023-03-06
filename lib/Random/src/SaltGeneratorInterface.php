<?php

declare(strict_types=1);

namespace RZ\Roadiz\Random;

interface SaltGeneratorInterface
{
    /**
     * @return string
     */
    public function generateSalt();
}
