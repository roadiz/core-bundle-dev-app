<?php

declare(strict_types=1);

namespace RZ\Roadiz\CoreBundle\Form\Constraint;

use Symfony\Component\Validator\Constraint;

final class UniqueTagName extends Constraint
{
    public mixed $currentValue = null;
    public string $message = 'tagName.%name%.alreadyExists';
}
