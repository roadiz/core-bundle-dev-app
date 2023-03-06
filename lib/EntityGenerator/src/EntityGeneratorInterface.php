<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface EntityGeneratorInterface extends ClassGeneratorInterface
{
    public function configureOptions(OptionsResolver $resolver): void;
}
