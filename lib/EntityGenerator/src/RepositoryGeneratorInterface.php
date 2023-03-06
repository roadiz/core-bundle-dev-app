<?php

declare(strict_types=1);

namespace RZ\Roadiz\EntityGenerator;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface RepositoryGeneratorInterface extends ClassGeneratorInterface
{
    public function configureOptions(OptionsResolver $resolver): void;
}
