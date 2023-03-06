<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

interface ClassGeneratorInterface
{
    public function getClassContent(): string;
}
