<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

class ValidYaml extends Constraint
{
    public string $message = 'yaml.is.not.valid.{{ error }}';
}
